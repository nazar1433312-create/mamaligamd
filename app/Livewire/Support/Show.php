<?php

namespace App\Livewire\Support;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\Support\SupportNotifier;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Show extends Component
{
    public SupportTicket $ticket;

    public string $newMessage = '';

    public function mount(SupportTicket $ticket): void
    {
        abort_unless($ticket->user_id === Auth::id(), 403);

        $this->ticket = $ticket;
    }

    public function reply(SupportNotifier $notifier): void
    {
        $this->validate(['newMessage' => ['required', 'string', 'max:2000']]);

        $this->ticket->messages()->create([
            'sender_type' => SupportMessage::SENDER_USER,
            'body' => $this->newMessage,
        ]);

        $notifier->notifyNewMessage($this->ticket, $this->newMessage);

        $this->newMessage = '';
    }

    public function render()
    {
        return view('livewire.support.show', [
            'messages' => $this->ticket->messages()->orderBy('created_at')->get(),
        ]);
    }
}
