<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_id',
        'verified_by',
        'verification_type',
        'ip_address',
        'user_agent',
        'verified_at',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Log verification attempt.
     */
    public static function logVerification(Certificate $certificate, string $type = 'manual', string $result = 'valid'): self
    {
        return static::create([
            'certificate_id' => $certificate->id,
            'verified_by' => auth()->id(),
            'verification_type' => $type,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'verified_at' => now(),
            'result' => $result,
        ]);
    }
}
