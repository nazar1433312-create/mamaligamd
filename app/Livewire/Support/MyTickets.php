<?php

namespace App\Livewire\Support;

use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class MyTickets extends Component
{
    public function render()
    {
        return view('livewire.support.my-tickets', [
            'tickets' => SupportTicket::where('user_id', Auth::id())->latest()->get(),
        ]);
    }
}
