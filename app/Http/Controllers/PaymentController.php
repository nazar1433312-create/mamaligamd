<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\VictoriaBank\VictoriaBankClient;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function checkout(Order $order, VictoriaBankClient $bank): never
    {
        abort_unless($order->customer_id === Auth::id(), 403);
        abort_unless($order->status === Order::STATUS_IN_PROGRESS, 403, 'Заказ не готов к оплате.');
        abort_if($order->paid_at, 403, 'Заказ уже оплачен.');
        abort_unless(Auth::user()->is_verified, 403, 'Для оплаты картой нужно пройти верификацию.');
        abort_unless($order->acceptedOffer, 404, 'У заказа нет принятого отклика.');

        $amount = (float) $order->acceptedOffer->price;

        // Reuse an already-pending payment for this order instead of
        // creating a new row every time — a double-click or two open tabs
        // would otherwise create duplicate pending payments/card holds.
        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id, 'type' => Payment::TYPE_CHARGE, 'status' => Payment::STATUS_PENDING],
            ['provider' => 'victoriabank', 'amount' => $amount]
        );

        if (! $payment->provider_payment_id) {
            $payment->update(['provider_payment_id' => sprintf('JP%08d', $payment->id)]);
        }

        $bank->authorize(
            $payment->provider_payment_id,
            (float) $payment->amount,
            route('payments.victoriabank.callback', $payment),
            "Оплата заказа #{$order->id}"
        );
    }
}
