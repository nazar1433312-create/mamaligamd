<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'password', 'telegram_id', 'telegram_username',
    'phone', 'city_id', 'avatar_path', 'about', 'is_executor',
    'is_verified', 'is_banned', 'rating_count', 'rating_avg',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_executor' => 'boolean',
            'is_admin' => 'boolean',
            'is_banned' => 'boolean',
            'is_verified' => 'boolean',
            'rating_avg' => 'decimal:2',
            'history_paid_at' => 'datetime',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'executor_id');
    }

    public function completedJobs(): HasMany
    {
        return $this->hasMany(Offer::class, 'executor_id')
            ->where('status', Offer::STATUS_ACCEPTED)
            ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_COMPLETED));
    }

    public function payoutMethods(): HasMany
    {
        return $this->hasMany(PayoutMethod::class);
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'target_id');
    }

    public function reviewsWritten(): HasMany
    {
        return $this->hasMany(Review::class, 'author_id');
    }

    public function verificationRequests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteOrders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'favorites')->withTimestamps();
    }

    public function reportsFiled(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reportsReceived(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    public function callsAsCaller(): HasMany
    {
        return $this->hasMany(Call::class, 'caller_id');
    }

    public function callsAsCallee(): HasMany
    {
        return $this->hasMany(Call::class, 'callee_id');
    }
}
