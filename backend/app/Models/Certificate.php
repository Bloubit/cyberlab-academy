<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'lab_id',
        'certificate_number',
        'certificate_type',
        'title',
        'description',
        'issued_at',
        'expires_at',
        'pdf_url',
        'qr_code',
        'verification_url',
        'metadata',
        'revoked',
        'revoked_at',
        'revoked_reason',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked' => 'boolean',
            'revoked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * Generate unique certificate number.
     */
    public static function generateNumber(): string
    {
        return 'CERT-' . strtoupper(Str::random(4)) . '-' . date('Y') . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create certificate for course completion.
     */
    public static function createForCourse(User $user, Course $course): self
    {
        $number = static::generateNumber();

        return static::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'certificate_number' => $number,
            'certificate_type' => 'course_completion',
            'title' => "Certificate of Completion: {$course->title}",
            'description' => "This certifies that {$user->name} has successfully completed the course {$course->title}.",
            'issued_at' => now(),
            'verification_url' => route('certificates.verify', $number),
        ]);
    }

    /**
     * Create certificate for lab completion.
     */
    public static function createForLab(User $user, Lab $lab): self
    {
        $number = static::generateNumber();

        return static::create([
            'user_id' => $user->id,
            'lab_id' => $lab->id,
            'certificate_number' => $number,
            'certificate_type' => 'lab_completion',
            'title' => "Certificate of Achievement: {$lab->title}",
            'description' => "This certifies that {$user->name} has successfully completed the lab {$lab->title}.",
            'issued_at' => now(),
            'verification_url' => route('certificates.verify', $number),
        ]);
    }

    /**
     * Verify certificate.
     */
    public function verify(): array
    {
        return [
            'valid' => !$this->revoked && (!$this->expires_at || $this->expires_at->isFuture()),
            'certificate' => $this,
            'user' => $this->user,
            'course' => $this->course,
            'lab' => $this->lab,
        ];
    }

    /**
     * Revoke certificate.
     */
    public function revoke(string $reason = null): void
    {
        $this->update([
            'revoked' => true,
            'revoked_at' => now(),
            'revoked_reason' => $reason,
        ]);
    }
}
