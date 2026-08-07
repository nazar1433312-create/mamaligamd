<?php

namespace App\Livewire\Admin;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\Support\SupportNotifier;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Support extends Component
{
    public ?int $openTicketId = null;

    public string $reply = '';

    public function open(int $ticketId): void
    {
        $this->openTicketId = $ticketId;
        $this->reply = '';
    }

    public function sendReply(SupportNotifier $notifier): void
    {
        $this->validate(['reply' => ['required', 'string', 'max:2000']]);

        $ticket = SupportTicket::findOrFail($this->openTicketId);

        $ticket->messages()->create([
            'sender_type' => SupportMessage::SENDER_ADMIN,
            'body' => $this->reply,
        ]);

        if ($ticket->user?->telegram_id) {
            app(\App\Services\Telegram\TelegramApi::class)->sendMessage(
                $ticket->user->telegram_id,
                "💬 Ответ по вашему обращению #{$ticket->id} \"{$ticket->subject}\":\n\n{$this->reply}"
            );
        }

        $this->reply = '';
    }

    public function close(int $ticketId): void
    {
        SupportTicket::whereKey($ticketId)->update(['status' => SupportTicket::STATUS_CLOSED]);
    }

    public function render()
    {
        return view('livewire.admin.support', [
            'tickets' => SupportTicket::with('user')->latest()->get(),
            'openTicket' => $this->openTicketId
                ? SupportTicket::with(['messages' => fn ($q) => $q->orderBy('created_at')])->find($this->openTicketId)
                : null,
        ]);
    }
}
