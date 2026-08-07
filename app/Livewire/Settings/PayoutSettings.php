<?php

namespace App\Livewire\Settings;

use App\Models\PayoutMethod;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class PayoutSettings extends Component
{
    public string $cardNumber = '';

    public function save(): void
    {
        $this->validate([
            'cardNumber' => ['required', 'digits_between:13,19'],
        ]);

        PayoutMethod::storeCard(Auth::user(), $this->cardNumber);

        $this->cardNumber = '';
        session()->flash('status', 'Карта для выплат сохранена.');
    }

    public function render()
    {
        return view('livewire.settings.payout-settings', [
            'current' => Auth::user()->payoutMethods()->where('is_default', true)->first(),
        ]);
    }
}
