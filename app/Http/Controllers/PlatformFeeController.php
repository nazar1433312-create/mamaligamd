<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\LiqPay\LiqPayClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlatformFeeController extends Controller
{
    public function checkout(Order $order, LiqPayClient $liqPay): View
    {
        abort_unless($order->customer_id === Auth::id(), 403);
        abort_unless($order->status === Order::STATUS_PENDING_PAYMENT, 403, 'Заказ уже опубликован или недоступен.');

        $amount = (float) config('services.platform.fee_amount');

        $payment = Payment::create([
            'order_id' => $order->id,
            'type' => Payment::TYPE_PLATFORM_FEE,
            'amount' => $amount,
            'status' => Payment::STATUS_PENDING,
        ]);

        $fields = $liqPay->checkoutFields([
            'action' => 'pay',
            'amount' => $amount,
            'currency' => 'MDL',
            'description' => "Публикация заказа: {$order->title}",
            'order_id' => "platformfee-order-{$order->id}-payment-{$payment->id}",
            'result_url' => route('orders.show', $order),
            'server_url' => route('payments.liqpay.callback'),
            'sandbox' => config('services.liqpay.sandbox') ? 1 : 0,
        ]);

        return view('payments.liqpay-redirect', $fields);
    }
}
