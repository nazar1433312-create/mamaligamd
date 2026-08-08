<?php

namespace App\Livewire\Settings;

use App\Models\Call;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class History extends Component
{
    public function render()
    {
        $user = Auth::user();

        $calls = Call::with(['caller', 'callee'])
            ->where('caller_id', $user->id)
            ->orWhere('callee_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('livewire.settings.history', [
            'user' => $user,
            'calls' => $calls,
            'feeAmount' => config('services.platform.history_fee_amount'),
        ]);
    }
}
