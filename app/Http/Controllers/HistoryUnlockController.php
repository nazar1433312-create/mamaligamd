<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\VictoriaBank\VictoriaBankClient;
use Illuminate\Support\Facades\Auth;

class HistoryUnlockController extends Controller
{
    public function checkout(VictoriaBankClient $bank): never
    {
        abort_if(Auth::user()->history_paid_at, 403, 'История уже сохраняется навсегда.');

        $amount = (float) config('services.platform.history_fee_amount');

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'type' => Payment::TYPE_HISTORY_UNLOCK,
            'provider' => 'victoriabank',
            'amount' => $amount,
            'status' => Payment::STATUS_PENDING,
        ]);

        $orderCode = sprintf('HU%08d', $payment->id);
        $payment->update(['provider_payment_id' => $orderCode]);

        $bank->authorize(
            $orderCode,
            $amount,
            route('payments.victoriabank.callback', $payment),
            'Сохранение переписки и звонков навсегда'
        );
    }
}
