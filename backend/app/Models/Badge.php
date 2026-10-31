<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'category',
        'rarity',
        'xp_reward',
        'requirements',
        'is_active',
        'is_hidden',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'xp_reward' => 'integer',
            'requirements' => 'array',
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at', 'source_type', 'source_id')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByRarity($query, string $rarity)
    {
        return $query->where('rarity', $rarity);
    }

    /**
     * Check if user has earned this badge.
     */
    public function isEarnedBy(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Award badge to user.
     */
    public function awardTo(User $user, string $sourceType = null, $sourceId = null): void
    {
        if (!$this->isEarnedBy($user)) {
            $this->users()->attach($user->id, [
                'earned_at' => now(),
                'source_type' => $sourceType,
                'source_id' => $sourceId,
            ]);

            // Award XP
            if ($this->xp_reward > 0) {
                XpHistory::award($user, $this->xp_reward, 'badge', $this->id, "Earned badge: {$this->name}");
            }
        }
    }
}
