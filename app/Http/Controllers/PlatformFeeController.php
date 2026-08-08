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

        $payment = Payment::create([
            'order_id' => $order->id,
            'type' => Payment::TYPE_PLATFORM_FEE,
            'provider' => 'victoriabank',
            'amount' => $amount,
            'status' => Payment::STATUS_PENDING,
        ]);

        $orderCode = sprintf('PF%08d', $payment->id);
        $payment->update(['provider_payment_id' => $orderCode]);

        $bank->authorize(
            $orderCode,
            $amount,
            route('payments.victoriabank.callback', $payment),
            "Публикация заказа #{$order->id}"
        );
    }
}
