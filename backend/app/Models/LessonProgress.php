<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'status',
        'progress',
        'time_spent_seconds',
        'last_position_seconds',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'decimal:2',
            'time_spent_seconds' => 'integer',
            'last_position_seconds' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
        ]);
    }

    public function updateProgress(float $progress, int $timeSpent = 0): void
    {
        $this->update([
            'progress' => min($progress, 100),
            'time_spent_seconds' => $this->time_spent_seconds + $timeSpent,
            'status' => $progress >= 100 ? 'completed' : 'in_progress',
            'completed_at' => $progress >= 100 ? now() : null,
        ]);
    }
}
