<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'stripe_subscription_id',
        'stripe_customer_id',
        'trial_start',
        'trial_end',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
        'ends_at',
        'payment_method',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'trial_start' => 'datetime',
            'trial_end' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'cancelled_at' => 'datetime',
            'ends_at' => 'datetime',
            'payment_method' => 'array',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('current_period_end', '>', now());
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
               $this->current_period_end &&
               $this->current_period_end->isFuture();
    }

    public function isTrial(): bool
    {
        return $this->status === 'trialing' &&
               $this->trial_end &&
               $this->trial_end->isFuture();
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Cancel subscription.
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'ends_at' => $this->current_period_end,
        ]);
    }
}
