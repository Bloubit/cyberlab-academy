<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_id',
        'user_id',
        'status',
        'container_id',
        'connection_info',
        'resources',
        'started_at',
        'expires_at',
        'stopped_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'started_at' => 'datetime',
            'stopped_at' => 'datetime',
            'connection_info' => 'array',
            'resources' => 'array',
        ];
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function start(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
            'expires_at' => now()->addHours(4),
        ]);
    }

    public function stop(): void
    {
        $this->update([
            'status' => 'stopped',
            'stopped_at' => now(),
        ]);
    }
}
