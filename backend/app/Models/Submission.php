<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'challenge_id',
        'flag_id',
        'submitted_flag',
        'is_correct',
        'points_earned',
        'attempt_number',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'points_earned' => 'integer',
            'attempt_number' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function flag(): BelongsTo
    {
        return $this->belongsTo(Flag::class);
    }

    /**
     * Create a new submission and validate the flag.
     */
    public static function submit(User $user, Challenge $challenge, string $flagValue): self
    {
        $isCorrect = false;
        $matchedFlag = null;

        foreach ($challenge->flags as $flag) {
            if ($flag->verify($flagValue)) {
                $isCorrect = true;
                $matchedFlag = $flag;
                break;
            }
        }

        // Determine attempt number
        $attemptNumber = static::where('user_id', $user->id)
            ->where('challenge_id', $challenge->id)
            ->max('attempt_number') ?? 0;
        $attemptNumber++;

        return static::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'flag_id' => $matchedFlag?->id,
            'submitted_flag' => $flagValue,
            'is_correct' => $isCorrect,
            'points_earned' => $isCorrect ? $challenge->points : 0,
            'attempt_number' => $attemptNumber,
            'submitted_at' => now(),
        ]);
    }
}
