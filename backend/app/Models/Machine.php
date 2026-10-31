<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_id',
        'name',
        'image',
        'os',
        'ip_address',
        'credentials',
        'ports',
        'services',
        'cpu_cores',
        'ram_mb',
        'disk_gb',
        'provisioning_script',
    ];

    protected function casts(): array
    {
        return [
            'cpu_cores' => 'integer',
            'ram_mb' => 'integer',
            'disk_gb' => 'integer',
            'credentials' => 'encrypted:array',
            'ports' => 'array',
            'services' => 'array',
        ];
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }
}
