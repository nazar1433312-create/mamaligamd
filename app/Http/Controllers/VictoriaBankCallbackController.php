<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Telegram\TelegramApi;
use App\Services\Telegram\UserNotifier;
use App\Services\VictoriaBank\VictoriaBankClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class VictoriaBankCallbackController extends Controller
{
    /**
     * Victoriabank posts the transaction result back to this same URL that
     * the customer's browser is redirected to after paying (BACKREF) — there
     * is no separate server-to-server webhook in this gateway, so this only
     * fires if the customer's browser makes it back here.
     */
    public function __invoke(Request $request, Payment $payment, VictoriaBankClient $bank, UserNotifier $notifier, TelegramApi $telegram): RedirectResponse
    {
        $redirectTo = $this->redirectFor($payment);

        $response = null;
        $success = false;

        try {
            $response = $bank->parseResponse($request->all());
            $success = $response->isValid();
        } catch (Throwable $e) {
            report($e);
        }

        // The response is a genuinely bank-signed payload, but it isn't
        // necessarily for THIS payment: without checking ORDER/AMOUNT, a
        // valid response captured for one payment could be replayed against
        // any other {payment} in the URL to fraudulently mark it paid.
        if ($success) {
            if ((string) $response->ORDER !== (string) $payment->provider_payment_id) {
                Log::warning('Victoriabank callback: ORDER mismatch, possible replay', [
                    'payment_id' => $payment->id,
                    'expected_order' => $payment->provider_payment_id,
                    'got_order' => $response->ORDER,
                ]);
                $success = false;
            } elseif (abs((float) $response->AMOUNT - (float) $payment->amount) > 0.01) {
                Log::warning('Victoriabank callback: AMOUNT mismatch, possible replay', [
                    'payment_id' => $payment->id,
                    'expected_amount' => (float) $payment->amount,
                    'got_amount' => (float) $response->AMOUNT,
                ]);
                $success = false;
            }
        }

        // Lock the row and re-check status inside the transaction so two
        // concurrent/duplicate callbacks for the same payment can't both
        // pass the "not yet processed" check and double-run side effects.
        $payment = DB::transaction(function () use ($payment, $success, $request) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (in_array($locked->status, [Payment::STATUS_SUCCESS, Payment::STATUS_FAILED], true)) {
                return null;
            }

            $locked->update([
                'status' => $success ? Payment::STATUS_SUCCESS : Payment::STATUS_FAILED,
                'raw_response' => $request->all(),
            ]);

            return $locked;
        });

        if (! $payment) {
            return redirect($redirectTo);
        }

        if (! $success) {
            Log::warning('Victoriabank callback: payment not approved', [
                'payment_id' => $payment->id,
                'error' => $response?->getLastError(),
            ]);

            return redirect($redirectTo);
        }

        try {
            $bank->requestCompletion(
                (string) $response->ORDER,
                (float) $response->AMOUNT,
                (string) $response->RRN,
                (string) $response->INT_REF,
            );
        } catch (Throwable $e) {
            report($e);

            $adminChatId = config('services.telegram.admin_chat_id');
            if ($adminChatId) {
                $telegram->sendMessage($adminChatId, "⚠️ Не удалось списать (capture) платёж #{$payment->id} на {$payment->amount} MDL после успешной авторизации. Проверьте вручную в личном кабинете Victoriabank.");
            }
        }

        match ($payment->type) {
            Payment::TYPE_PLATFORM_FEE => $this->handlePlatformFeePaid($payment, $notifier),
            Payment::TYPE_CHARGE => $this->handleJobPaymentPaid($payment, $notifier),
            Payment::TYPE_HISTORY_UNLOCK => $this->handleHistoryUnlockPaid($payment, $notifier),
            default => null,
        };

        return redirect($redirectTo);
    }

    private function redirectFor(Payment $payment): string
    {
        if ($payment->order_id) {
            return route('orders.show', $payment->order_id);
        }

        return route('settings.history');
    }

    private function handlePlatformFeePaid(Payment $payment, UserNotifier $notifier): void
    {
        $order = $payment->order;

        if (! $order || $order->status !== Order::STATUS_PENDING_PAYMENT) {
            return;
        }

        $order->update([
            'status' => Order::STATUS_OPEN,
            'platform_fee_paid_at' => now(),
        ]);

        $notifier->notify($order->customer, "✅ Ваш заказ #{$order->id} \"{$order->title}\" оплачен и опубликован!");

        $this->notifyInterestedExecutors($order, $notifier);
    }

    /**
     * Ping executors who've previously made an offer in this category —
     * otherwise the only way they'd learn about a new matching order is by
     * happening to browse the site again.
     */
    private function notifyInterestedExecutors(Order $order, UserNotifier $notifier): void
    {
        $executorIds = Offer::whereHas('order', fn ($q) => $q->where('category_id', $order->category_id))
            ->where('executor_id', '!=', $order->customer_id)
            ->distinct()
            ->pluck('executor_id');

        if ($executorIds->isEmpty()) {
            return;
        }

        $budget = $order->budget_min
            ? number_format($order->budget_min, 0, ',', ' ').' MDL'
            : 'договорная цена';

        $text = "🆕 Новый заказ в категории \"{$order->category->name}\": \"{$order->title}\" — {$budget}.\n"
            .rtrim(config('app.url'), '/')."/orders/{$order->id}";

        User::whereIn('id', $executorIds)
            ->whereNotNull('telegram_id')
            ->get()
            ->each(fn (User $executor) => $notifier->notify($executor, $text));
    }

    private function handleJobPaymentPaid(Payment $payment, UserNotifier $notifier): void
    {
        $order = $payment->order;

        if (! $order) {
            return;
        }

        $order->update(['paid_at' => now()]);

        $notifier->notify(
            $order->loadMissing('acceptedOffer.executor')->acceptedOffer?->executor,
            "💳 Оплата картой по заказу #{$order->id} \"{$order->title}\" прошла успешно."
        );
    }

    private function handleHistoryUnlockPaid(Payment $payment, UserNotifier $notifier): void
    {
        $user = $payment->user;

        if (! $user) {
            return;
        }

        $user->forceFill(['history_paid_at' => now()])->save();

        $notifier->notify($user, '✅ Готово! Переписка и звонки теперь сохраняются навсегда.');
    }
}
