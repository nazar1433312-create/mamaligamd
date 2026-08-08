<?php

namespace App\Livewire\Admin;

use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Payouts extends Component
{
    public function markPaid(int $paymentId): void
    {
        Payment::whereKey($paymentId)->update(['status' => Payment::STATUS_SUCCESS]);
    }

    public function render()
    {
        $payouts = Payment::where('type', Payment::TYPE_PAYOUT)
            ->with(['order.acceptedOffer.executor.payoutMethods'])
            ->latest()
            ->get();

        return view('livewire.admin.payouts', [
            'pending' => $payouts->where('status', Payment::STATUS_PENDING),
            'paid' => $payouts->where('status', Payment::STATUS_SUCCESS)->take(30),
        ]);
    }
}
