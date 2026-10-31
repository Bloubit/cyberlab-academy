<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'avatar',
        'bio',
        'phone',
        'country_code',
        'timezone',
        'language',
        'is_active',
        'is_banned',
        'ban_reason',
        'last_login_at',
        'last_login_ip',
        'login_attempts',
        'email_verified_at',
        'settings',
        'metadata',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'settings' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
            'is_banned' => 'boolean',
            'login_attempts' => 'integer',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    /**
     * Assign a role to user via DB directly.
     */
    public function assignRole(string $role): void
    {
        $roleRecord = DB::table('roles')->where('slug', $role)->first();

        if ($roleRecord) {
            DB::table('user_roles')->updateOrInsert([
                'user_id' => $this->id,
                'role_id' => $roleRecord->id,
            ]);
        }
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $this->id)
            ->where('roles.slug', $role)
            ->exists();
    }

    /**
     * Get user's role slugs.
     */
    public function getRoles(): array
    {
        return DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $this->id)
            ->pluck('roles.slug')
            ->toArray();
    }

    /**
     * Check if user has a specific permission (simplified).
     */
    public function hasPermission(string $permission): bool
    {
        // Admin roles have all permissions
        $adminRoles = ['super-admin', 'admin'];
        foreach ($adminRoles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return DB::table('user_roles')
            ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('user_roles.user_id', $this->id)
            ->where('permissions.slug', $permission)
            ->exists();
    }

    // Relationships

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    public function xpSummary(): HasOne
    {
        return $this->hasOne(UserXpSummary::class);
    }

    public function xpHistory(): HasMany
    {
        return $this->hasMany(XpHistory::class);
    }

    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_enrollments')
            ->withPivot('status', 'progress', 'enrolled_at', 'completed_at')
            ->withTimestamps();
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    public function labInstances(): HasMany
    {
        return $this->hasMany(LabInstance::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at', 'source_type', 'source_id')
            ->withTimestamps();
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function activityLog(): HasMany
    {
        return $this->hasMany(UserActivityLog::class);
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class);
    }

    /**
     * Get user's total XP.
     */
    public function getTotalXpAttribute(): int
    {
        return $this->xpSummary?->total_xp ?? 0;
    }

    /**
     * Get user's level.
     */
    public function getLevelAttribute(): int
    {
        return $this->xpSummary?->level ?? 1;
    }

    /**
     * Get user's active subscription.
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('current_period_end', '>', now())
            ->first();
    }

    /**
     * Check if user has active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Check if user is instructor.
     */
    public function isInstructor(): bool
    {
        return $this->hasRole('instructor') || $this->hasRole('admin');
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('super-admin');
    }
}
