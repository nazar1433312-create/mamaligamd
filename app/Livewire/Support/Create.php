<?php

namespace App\Livewire\Support;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\Support\SupportNotifier;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Create extends Component
{
    public string $guestName = '';

    public string $guestContact = '';

    public string $subject = '';

    public string $message = '';

    public bool $sent = false;

    protected function rules(): array
    {
        $rules = [
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ];

        if (! Auth::check()) {
            $rules['guestName'] = ['required', 'string', 'max:100'];
            $rules['guestContact'] = ['required', 'string', 'max:150'];
        }

        return $rules;
    }

    public function send(SupportNotifier $notifier)
    {
        $this->validate();

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'guest_name' => Auth::check() ? null : $this->guestName,
            'guest_contact' => Auth::check() ? null : $this->guestContact,
            'subject' => $this->subject,
        ]);

        $ticket->messages()->create([
            'sender_type' => SupportMessage::SENDER_USER,
            'body' => $this->message,
        ]);

        $notifier->notifyNewTicket($ticket, $this->message);

        if (Auth::check()) {
            return $this->redirect(route('support.show', $ticket), navigate: true);
        }

        $this->sent = true;
        $this->reset(['subject', 'message']);
    }

    public function render()
    {
        return view('livewire.support.create');
    }
}
