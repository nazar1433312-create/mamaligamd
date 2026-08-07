<?php

namespace App\Livewire\Messages;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Show extends Component
{
    public User $otherUser;

    public string $newMessage = '';

    public function mount(User $user): void
    {
        abort_if($user->id === Auth::id(), 403);

        $this->otherUser = $user;
    }

    public function send(): void
    {
        $this->validate(['newMessage' => ['required', 'string', 'max:2000']]);

        Message::create([
            'order_id' => null,
            'sender_id' => Auth::id(),
            'recipient_id' => $this->otherUser->id,
            'body' => $this->newMessage,
        ]);

        $this->newMessage = '';
    }

    public function render()
    {
        $userId = Auth::id();

        $messages = Message::whereNull('order_id')
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)->where('recipient_id', $this->otherUser->id);
            })
            ->orWhere(function ($q) use ($userId) {
                $q->whereNull('order_id')
                    ->where('sender_id', $this->otherUser->id)
                    ->where('recipient_id', $userId);
            })
            ->orderBy('created_at')
            ->get();

        Message::whereNull('order_id')
            ->where('sender_id', $this->otherUser->id)
            ->where('recipient_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('livewire.messages.show', ['messages' => $messages]);
    }
}
