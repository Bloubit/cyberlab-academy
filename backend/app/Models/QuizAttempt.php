<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'score',
        'total_points',
        'is_passed',
        'time_taken_seconds',
        'started_at',
        'completed_at',
        'answers',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'total_points' => 'decimal:2',
            'is_passed' => 'boolean',
            'time_taken_seconds' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'answers' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function complete(array $answers, float $score): void
    {
        $this->update([
            'answers' => $answers,
            'score' => $score,
            'total_points' => $this->quiz->total_points,
            'is_passed' => $score >= $this->quiz->passing_score,
            'completed_at' => now(),
            'time_taken_seconds' => $this->started_at->diffInSeconds(now()),
        ]);
    }
}
