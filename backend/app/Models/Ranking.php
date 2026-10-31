<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    use HasFactory;

    protected $fillable = [
        'ranking_type',
        'period',
        'user_id',
        'score',
        'rank',
        'metadata',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'rank' => 'integer',
            'metadata' => 'array',
            'calculated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get global leaderboard.
     */
    public static function getGlobalLeaderboard(string $period = 'all_time', int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('ranking_type', 'global')
            ->where('period', $period)
            ->orderBy('rank')
            ->limit($limit)
            ->get();
    }

    /**
     * Update global rankings.
     */
    public static function updateGlobalRankings(): void
    {
        $users = User::with('xpSummary')
            ->where('is_active', true)
            ->get()
            ->sortByDesc(fn($u) => $u->xpSummary?->total_xp ?? 0)
            ->values();

        foreach ($users as $index => $user) {
            static::updateOrCreate(
                [
                    'ranking_type' => 'global',
                    'period' => 'all_time',
                    'user_id' => $user->id,
                ],
                [
                    'score' => $user->xpSummary?->total_xp ?? 0,
                    'rank' => $index + 1,
                    'calculated_at' => now(),
                ]
            );
        }
    }
}
