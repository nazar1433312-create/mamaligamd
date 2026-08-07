<?php

namespace App\Services\LiqPay;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PayoutService
{
    public function __construct(
        private readonly LiqPayClient $liqPay
    ) {}

    /**
     * Pay out the executor for a completed order, withholding the platform commission.
     * Returns the created Payment record, or null if no payout method is on file.
     */
    public function payoutForOrder(Order $order): ?Payment
    {
        $executor = $order->acceptedOffer?->executor;
        $payoutMethod = $executor?->payoutMethods()->where('is_default', true)->first();

        if (! $executor || ! $payoutMethod) {
            Log::warning("Payout skipped for order {$order->id}: executor has no payout method on file.");

            return null;
        }

        $grossAmount = (float) $order->acceptedOffer->price;
        $commissionPercent = (float) ($order->commission_percent ?? config('services.platform.commission_percent'));
        $commissionAmount = round($grossAmount * $commissionPercent / 100, 2);
        $netAmount = round($grossAmount - $commissionAmount, 2);

        $payment = Payment::create([
            'order_id' => $order->id,
            'type' => Payment::TYPE_PAYOUT,
            'amount' => $netAmount,
            'commission_amount' => $commissionAmount,
            'status' => Payment::STATUS_PENDING,
        ]);

        $response = $this->liqPay->request([
            'action' => 'paytocard',
            'amount' => $netAmount,
            'currency' => 'UAH',
            'description' => "Выплата за заказ #{$order->id}",
            'order_id' => "payout-order-{$order->id}-payment-{$payment->id}",
            'card' => $payoutMethod->card_number_encrypted,
            'sandbox' => config('services.liqpay.sandbox') ? 1 : 0,
        ]);

        $success = ($response['result'] ?? null) === 'ok' || ($response['status'] ?? null) === 'success';

        $payment->update([
            'status' => $success ? Payment::STATUS_SUCCESS : Payment::STATUS_FAILED,
            'provider_payment_id' => (string) ($response['payment_id'] ?? ''),
            'raw_response' => $response,
        ]);

        return $payment;
    }
}
