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

        $amount = (float) $order->acceptedOffer->price;

        $payment = Payment::create([
            'order_id' => $order->id,
            'type' => Payment::TYPE_CHARGE,
            'provider' => 'victoriabank',
            'amount' => $amount,
            'status' => Payment::STATUS_PENDING,
        ]);

        $orderCode = sprintf('JP%08d', $payment->id);
        $payment->update(['provider_payment_id' => $orderCode]);

        $bank->authorize(
            $orderCode,
            $amount,
            route('payments.victoriabank.callback', $payment),
            "Оплата заказа #{$order->id}"
        );
    }
}
