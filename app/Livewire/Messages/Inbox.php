<?php

namespace App\Livewire\Messages;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Inbox extends Component
{
    public function render()
    {
        $userId = Auth::id();

        $messages = Message::whereNull('order_id')
            ->where(fn ($q) => $q->where('sender_id', $userId)->orWhere('recipient_id', $userId))
            ->with(['sender', 'recipient'])
            ->latest()
            ->get();

        $unreadCounts = Message::whereNull('order_id')
            ->where('recipient_id', $userId)
            ->whereNull('read_at')
            ->selectRaw('sender_id, count(*) as total')
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        $conversations = $messages
            ->unique(fn ($m) => $m->sender_id === $userId ? $m->recipient_id : $m->sender_id)
            ->map(function ($m) use ($userId, $unreadCounts) {
                $other = $m->sender_id === $userId ? $m->recipient : $m->sender;

                return [
                    'user' => $other,
                    'last_message' => $m,
                    'unread' => $unreadCounts->get($other->id, 0),
                ];
            })
            ->values();

        return view('livewire.messages.inbox', ['conversations' => $conversations]);
    }
}
