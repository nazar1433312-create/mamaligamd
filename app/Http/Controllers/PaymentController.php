<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\LiqPay\LiqPayClient;
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

    public function callback(Request $request, LiqPayClient $liqPay): Response
    {
        $data = $request->input('data');
        $signature = $request->input('signature');

        if (! $data || ! $signature || ! $liqPay->verifySignature($data, $signature)) {
            Log::warning('LiqPay callback: invalid signature');

            return response()->noContent(403);
        }

        $payload = $liqPay->decode($data);

        if (! preg_match('/^order-(\d+)-payment-(\d+)$/', $payload['order_id'] ?? '', $m)) {
            return response()->noContent();
        }

        [$orderId, $paymentId] = [(int) $m[1], (int) $m[2]];

        $payment = Payment::find($paymentId);

        if (! $payment || $payment->order_id !== $orderId) {
            return response()->noContent();
        }

        $status = $payload['status'] ?? 'unknown';
        $success = in_array($status, ['success', 'sandbox'], true);

        $payment->update([
            'status' => $success ? Payment::STATUS_SUCCESS : Payment::STATUS_FAILED,
            'provider_payment_id' => (string) ($payload['payment_id'] ?? ''),
            'raw_response' => $payload,
        ]);

        if ($success) {
            $payment->order()->update(['paid_at' => now()]);
        }

        return response()->noContent();
    }
}
