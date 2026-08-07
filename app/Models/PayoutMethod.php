<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'card_masked', 'card_number_encrypted', 'card_token', 'is_default'])]
#[Hidden(['card_number_encrypted'])]
class PayoutMethod extends Model
{
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'card_number_encrypted' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function storeCard(User $user, string $cardNumber): self
    {
        $digits = preg_replace('/\D/', '', $cardNumber);
        $masked = substr($digits, 0, 6).str_repeat('*', max(strlen($digits) - 10, 0)).substr($digits, -4);

        return self::updateOrCreate(
            ['user_id' => $user->id, 'is_default' => true],
            ['card_masked' => $masked, 'card_number_encrypted' => $digits]
        );
    }
}
