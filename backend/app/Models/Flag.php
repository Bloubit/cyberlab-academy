<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class Flag extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'flag_hash',
        'flag_type',
        'validation_rules',
        'attempts_allowed',
    ];

    protected function casts(): array
    {
        return [
            'attempts_allowed' => 'integer',
            'validation_rules' => 'array',
        ];
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * Create a new flag with hashed value.
     */
    public static function createFlag(Challenge $challenge, string $flagValue, array $attributes = []): self
    {
        return static::create([
            'challenge_id' => $challenge->id,
            'flag_hash' => Hash::make($flagValue),
            'flag_type' => $attributes['flag_type'] ?? 'static',
            'validation_rules' => $attributes['validation_rules'] ?? null,
            'attempts_allowed' => $attributes['attempts_allowed'] ?? null,
        ]);
    }

    /**
     * Verify a submitted flag.
     */
    public function verify(string $submittedFlag): bool
    {
        return Hash::check($submittedFlag, $this->flag_hash);
    }
}
