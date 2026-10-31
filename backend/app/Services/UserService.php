<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserXpSummary;
use App\Models\XpHistory;
use App\Models\Badge;
use App\Models\Level;
use App\Models\Ranking;

class UserService
{
    /**
     * Get user profile.
     */
    public function getProfile(User $user): User
    {
        return $user->load([
            'xpSummary',
            'roles',
            'badges',
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    /**
     * Get user dashboard data.
     */
    public function getDashboard(User $user): array
    {
        return [
            'user' => $user,
            'xp_summary' => $user->xpSummary,
            'enrolled_courses' => $user->courseEnrollments()
                ->with('course')
                ->get(),
            'active_labs' => $user->labInstances()
                ->where('status', 'running')
                ->with('lab')
                ->get(),
            'recent_submissions' => $user->submissions()
                ->with('challenge')
                ->latest()
                ->limit(5)
                ->get(),
            'badges' => $user->badges()->latest('earned_at')->limit(5)->get(),
            'certificates' => $user->certificates()->latest('issued_at')->limit(5)->get(),
            'notifications' => $user->notifications()->unread()->limit(5)->get(),
        ];
    }

    /**
     * Award XP to user.
     */
    public function awardXp(User $user, int $amount, string $sourceType, $sourceId = null, string $description = null): XpHistory
    {
        $xp = XpHistory::award($user, $amount, $sourceType, $sourceId, $description);

        // Update XP summary
        $summary = UserXpSummary::firstOrCreate(
            ['user_id' => $user->id],
            ['total_xp' => 0, 'level' => 1]
        );
        $summary->addXp($amount);
        $summary->updateStreak();

        // Check for badge eligibility
        $this->checkBadges($user);

        return $xp;
    }

    /**
     * Check and award eligible badges.
     */
    public function checkBadges(User $user): void
    {
        $badges = Badge::active()->get();

        foreach ($badges as $badge) {
            if ($badge->isEarnedBy($user)) {
                continue;
            }

            $requirements = $badge->requirements;
            if (!$requirements) {
                continue;
            }

            $earned = false;

            switch ($requirements['type'] ?? null) {
                case 'labs_completed':
                    $count = $user->labInstances()
                        ->where('status', 'completed')
                        ->count();
                    $earned = $count >= ($requirements['count'] ?? 0);
                    break;

                case 'courses_completed':
                    $count = $user->courseEnrollments()
                        ->where('status', 'completed')
                        ->count();
                    $earned = $count >= ($requirements['count'] ?? 0);
                    break;

                case 'total_xp':
                    $earned = ($user->xpSummary?->total_xp ?? 0) >= ($requirements['amount'] ?? 0);
                    break;

                case 'streak':
                    $earned = ($user->xpSummary?->current_streak ?? 0) >= ($requirements['days'] ?? 0);
                    break;

                case 'flags_found':
                    $count = $user->submissions()
                        ->where('is_correct', true)
                        ->count();
                    $earned = $count >= ($requirements['count'] ?? 0);
                    break;
            }

            if ($earned) {
                $badge->awardTo($user, 'system', null);
            }
        }
    }

    /**
     * Get user leaderboard position.
     */
    public function getLeaderboardPosition(User $user): array
    {
        $totalXp = $user->xpSummary?->total_xp ?? 0;

        $rank = User::whereHas('xpSummary')
            ->where('is_active', true)
            ->where('xpSummary.total_xp', '>', $totalXp)
            ->count() + 1;

        return [
            'rank' => $rank,
            'total_xp' => $totalXp,
            'level' => $user->xpSummary?->level ?? 1,
        ];
    }

    /**
     * Get global leaderboard.
     */
    public function getLeaderboard(int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return User::with('xpSummary')
            ->where('is_active', true)
            ->orderByDesc('xpSummary.total_xp')
            ->limit($limit)
            ->get();
    }

    /**
     * Ban user.
     */
    public function ban(User $user, string $reason = null): User
    {
        $user->update([
            'is_banned' => true,
            'ban_reason' => $reason,
            'is_active' => false,
        ]);

        $user->tokens()->delete();

        return $user;
    }

    /**
     * Unban user.
     */
    public function unban(User $user): User
    {
        $user->update([
            'is_banned' => false,
            'ban_reason' => null,
            'is_active' => true,
        ]);

        return $user;
    }

    /**
     * Get user statistics.
     */
    public function getStats(User $user): array
    {
        return [
            'total_xp' => $user->xpSummary?->total_xp ?? 0,
            'level' => $user->xpSummary?->level ?? 1,
            'current_streak' => $user->xpSummary?->current_streak ?? 0,
            'longest_streak' => $user->xpSummary?->longest_streak ?? 0,
            'courses_enrolled' => $user->courseEnrollments()->count(),
            'courses_completed' => $user->courseEnrollments()->where('status', 'completed')->count(),
            'labs_completed' => $user->labInstances()->where('status', 'completed')->count(),
            'challenges_solved' => $user->submissions()->where('is_correct', true)->distinct('challenge_id')->count(),
            'badges_earned' => $user->badges()->count(),
            'certificates' => $user->certificates()->where('revoked', false)->count(),
        ];
    }
}
