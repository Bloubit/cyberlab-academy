<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lab extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'description',
        'difficulty_level',
        'type',
        'duration_minutes',
        'xp_reward',
        'learning_objectives',
        'hints',
        'network_topology',
        'is_published',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'xp_reward' => 'integer',
            'is_published' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(LabInstance::class);
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty_level', $difficulty);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isCompletedBy(User $user): bool
    {
        return $this->instances()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->exists();
    }
}
