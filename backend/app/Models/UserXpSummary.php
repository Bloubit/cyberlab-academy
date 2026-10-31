<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserXpSummary extends Model
{
    use HasFactory;

    protected $table = 'user_xp_summary';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'total_xp',
        'level',
        'xp_to_next_level',
        'current_streak',
        'longest_streak',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'total_xp' => 'integer',
            'level' => 'integer',
            'xp_to_next_level' => 'integer',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
            'last_activity_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Add XP and update level.
     */
    public function addXp(int $amount): void
    {
        $newTotal = $this->total_xp + $amount;
        $level = Level::where('min_xp', '<=', $newTotal)
            ->orderByDesc('min_xp')
            ->first();

        $this->update([
            'total_xp' => $newTotal,
            'level' => $level?->level ?? 1,
            'xp_to_next_level' => $level?->max_xp - $newTotal ?? 100,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Update streak.
     */
    public function updateStreak(): void
    {
        $lastActivity = $this->last_activity_at;
        $now = now();

        if ($lastActivity && $lastActivity->isToday()) {
            return;
        }

        if ($lastActivity && $lastActivity->isYesterday()) {
            $this->increment('current_streak');
        } else {
            $this->update(['current_streak' => 1]);
        }

        if ($this->current_streak > $this->longest_streak) {
            $this->update(['longest_streak' => $this->current_streak]);
        }
    }
}
