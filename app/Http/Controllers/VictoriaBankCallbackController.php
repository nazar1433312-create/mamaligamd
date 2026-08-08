<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Telegram\UserNotifier;
use App\Services\VictoriaBank\VictoriaBankClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function __invoke(Request $request, Payment $payment, VictoriaBankClient $bank, UserNotifier $notifier): RedirectResponse
    {
        $redirectTo = $this->redirectFor($payment);

        if (in_array($payment->status, [Payment::STATUS_SUCCESS, Payment::STATUS_FAILED], true)) {
            return redirect($redirectTo);
        }

        $response = null;
        $success = false;

        try {
            $response = $bank->parseResponse($request->all());
            $success = $response->isValid();
        } catch (Throwable $e) {
            report($e);
        }

        $payment->update([
            'status' => $success ? Payment::STATUS_SUCCESS : Payment::STATUS_FAILED,
            'raw_response' => $request->all(),
        ]);

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

        $user->update(['history_paid_at' => now()]);

        $notifier->notify($user, '✅ Готово! Переписка и звонки теперь сохраняются навсегда.');
    }
}
