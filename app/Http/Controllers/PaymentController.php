<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\LiqPay\LiqPayClient;
use App\Services\Telegram\UserNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function checkout(Order $order, LiqPayClient $liqPay): View
    {
        abort_unless($order->customer_id === Auth::id(), 403);
        abort_unless($order->status === Order::STATUS_IN_PROGRESS, 403, 'Заказ не готов к оплате.');
        abort_if($order->paid_at, 403, 'Заказ уже оплачен.');
        abort_unless(Auth::user()->is_verified, 403, 'Для оплаты картой нужно пройти верификацию.');

        $amount = $order->acceptedOffer->price;

        $payment = Payment::create([
            'order_id' => $order->id,
            'type' => Payment::TYPE_CHARGE,
            'amount' => $amount,
            'status' => Payment::STATUS_PENDING,
        ]);

        $fields = $liqPay->checkoutFields([
            'action' => 'pay',
            'amount' => (float) $amount,
            'currency' => 'MDL',
            'description' => "Оплата заказа #{$order->id}: {$order->title}",
            'order_id' => "order-{$order->id}-payment-{$payment->id}",
            'result_url' => route('orders.show', $order),
            'server_url' => route('payments.liqpay.callback'),
            'sandbox' => config('services.liqpay.sandbox') ? 1 : 0,
        ]);

        return view('payments.liqpay-redirect', $fields);
    }

    public function callback(Request $request, LiqPayClient $liqPay, UserNotifier $notifier): Response
    {
        $data = $request->input('data');
        $signature = $request->input('signature');

        if (! $data || ! $signature || ! $liqPay->verifySignature($data, $signature)) {
            Log::warning('LiqPay callback: invalid signature');

            return response()->noContent(403);
        }

        $payload = $liqPay->decode($data);
        $orderIdField = $payload['order_id'] ?? '';
        $status = $payload['status'] ?? 'unknown';
        $success = in_array($status, ['success', 'sandbox'], true);

        if (preg_match('/^platformfee-order-(\d+)-payment-(\d+)$/', $orderIdField, $m)) {
            $this->handlePlatformFeeCallback((int) $m[1], (int) $m[2], $success, $payload, $notifier);

            return response()->noContent();
        }

        if (preg_match('/^order-(\d+)-payment-(\d+)$/', $orderIdField, $m)) {
            $this->handleJobPaymentCallback((int) $m[1], (int) $m[2], $success, $payload, $notifier);

            return response()->noContent();
        }

        return response()->noContent();
    }

    private function handleJobPaymentCallback(int $orderId, int $paymentId, bool $success, array $payload, UserNotifier $notifier): void
    {
        $payment = Payment::find($paymentId);

        if (! $payment || $payment->order_id !== $orderId) {
            return;
        }

        $payment->update([
            'status' => $success ? Payment::STATUS_SUCCESS : Payment::STATUS_FAILED,
            'provider_payment_id' => (string) ($payload['payment_id'] ?? ''),
            'raw_response' => $payload,
        ]);

        if ($success) {
            $order = $payment->order()->first();
            $order->update(['paid_at' => now()]);

            $notifier->notify(
                $order->loadMissing('acceptedOffer.executor')->acceptedOffer?->executor,
                "💳 Оплата картой по заказу #{$order->id} \"{$order->title}\" прошла успешно."
            );
        }
    }

    private function handlePlatformFeeCallback(int $orderId, int $paymentId, bool $success, array $payload, UserNotifier $notifier): void
    {
        $payment = Payment::find($paymentId);

        if (! $payment || $payment->order_id !== $orderId) {
            return;
        }

        $payment->update([
            'status' => $success ? Payment::STATUS_SUCCESS : Payment::STATUS_FAILED,
            'provider_payment_id' => (string) ($payload['payment_id'] ?? ''),
            'raw_response' => $payload,
        ]);

        if (! $success) {
            return;
        }

        $order = Order::find($orderId);

        if (! $order || $order->status !== Order::STATUS_PENDING_PAYMENT) {
            return;
        }

        $order->update([
            'status' => Order::STATUS_OPEN,
            'platform_fee_paid_at' => now(),
        ]);

        $notifier->notify(
            $order->customer,
            "✅ Ваш заказ #{$order->id} \"{$order->title}\" оплачен и опубликован!"
        );
    }
}
