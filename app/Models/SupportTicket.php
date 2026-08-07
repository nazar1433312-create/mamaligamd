<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'guest_name', 'guest_contact', 'subject', 'status',
    'telegram_notify_chat_id', 'telegram_notify_message_id',
])]
class SupportTicket extends Model
{
    const STATUS_OPEN = 'open';

    const STATUS_CLOSED = 'closed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id');
    }

    public function contactLabel(): string
    {
        return $this->user?->name ?? $this->guest_name ?? 'Гость';
    }
}
