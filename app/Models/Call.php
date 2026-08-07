<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['caller_id', 'callee_id', 'status', 'offer_sdp', 'answer_sdp', 'started_at', 'ended_at'])]
class Call extends Model
{
    const STATUS_RINGING = 'ringing';

    const STATUS_ACCEPTED = 'accepted';

    const STATUS_DECLINED = 'declined';

    const STATUS_ENDED = 'ended';

    const STATUS_MISSED = 'missed';

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    public function callee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'callee_id');
    }

    public function signals(): HasMany
    {
        return $this->hasMany(CallSignal::class);
    }
}
