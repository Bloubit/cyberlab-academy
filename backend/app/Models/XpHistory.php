<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XpHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'source_type',
        'source_id',
        'description',
        'multiplier',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'multiplier' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Award XP to a user.
     */
    public static function award(User $user, int $amount, string $sourceType, $sourceId = null, string $description = null): self
    {
        return static::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'description' => $description,
            'multiplier' => 1.00,
        ]);
    }

    /**
     * Deduct XP from a user.
     */
    public static function deduct(User $user, int $amount, string $sourceType, $sourceId = null, string $description = null): self
    {
        return static::create([
            'user_id' => $user->id,
            'amount' => -$amount,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'description' => $description,
            'multiplier' => 1.00,
        ]);
    }
}
