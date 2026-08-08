<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id', 'user_id', 'type', 'provider', 'provider_payment_id',
    'amount', 'commission_amount', 'status', 'raw_response',
])]
class Payment extends Model
{
    const TYPE_CHARGE = 'charge';

    const TYPE_PAYOUT = 'payout';

    const TYPE_REFUND = 'refund';

    const TYPE_PLATFORM_FEE = 'platform_fee';

    const TYPE_HISTORY_UNLOCK = 'history_unlock';

    const STATUS_PENDING = 'pending';

    const STATUS_SUCCESS = 'success';

    const STATUS_FAILED = 'failed';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'raw_response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
