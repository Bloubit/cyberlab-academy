<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'integer';

    protected $fillable = [
        'level',
        'title',
        'min_xp',
        'max_xp',
        'badge_icon',
        'rewards',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'min_xp' => 'integer',
            'max_xp' => 'integer',
            'rewards' => 'array',
        ];
    }

    /**
     * Get level by XP amount.
     */
    public static function getByXp(int $xp): self
    {
        return static::where('min_xp', '<=', $xp)
            ->orderByDesc('min_xp')
            ->first();
    }

    /**
     * Get XP needed to reach next level.
     */
    public function getXpToNextLevel(int $currentXp): int
    {
        return max(0, $this->max_xp - $currentXp);
    }
}
