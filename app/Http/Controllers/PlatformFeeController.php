<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\VictoriaBank\VictoriaBankClient;
use Illuminate\Support\Facades\Auth;

class PlatformFeeController extends Controller
{
    public function checkout(Order $order, VictoriaBankClient $bank): never
    {
        abort_unless($order->customer_id === Auth::id(), 403);
        abort_unless($order->status === Order::STATUS_PENDING_PAYMENT, 403, 'Заказ уже опубликован или недоступен.');

        $amount = (float) config('services.platform.fee_amount');

        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id, 'type' => Payment::TYPE_PLATFORM_FEE, 'status' => Payment::STATUS_PENDING],
            ['provider' => 'victoriabank', 'amount' => $amount]
        );

        if (! $payment->provider_payment_id) {
            $payment->update(['provider_payment_id' => sprintf('PF%08d', $payment->id)]);
        }

        $bank->authorize(
            $payment->provider_payment_id,
            (float) $payment->amount,
            route('payments.victoriabank.callback', $payment),
            "Публикация заказа #{$order->id}"
        );
    }
}
