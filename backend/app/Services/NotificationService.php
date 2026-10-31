<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send notification to user.
     */
    public function send(User $user, string $type, string $title, string $message, array $data = []): Notification
    {
        return Notification::send($user, $type, $title, $message, $data);
    }

    /**
     * Send bulk notifications.
     */
    public function sendBulk(array $userIds, string $type, string $title, string $message, array $data = []): void
    {
        $users = User::whereIn('id', $userIds)->get();

        foreach ($users as $user) {
            $this->send($user, $type, $title, $message, $data);
        }
    }

    /**
     * Send notification to all users.
     */
    public function sendToAll(string $type, string $title, string $message, array $data = []): void
    {
        $users = User::where('is_active', true)->get();

        foreach ($users as $user) {
            $this->send($user, $type, $title, $message, $data);
        }
    }

    /**
     * Get user's notifications.
     */
    public function getUserNotifications(User $user, bool $unreadOnly = false): \Illuminate\Database\Eloquent\Builder
    {
        $query = Notification::where('user_id', $user->id);

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->orderByDesc('created_at');
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
    }

    /**
     * Mark all user notifications as read.
     */
    public function markAllAsRead(User $user): void
    {
        Notification::where('user_id', $user->id)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Delete notification.
     */
    public function delete(Notification $notification): bool
    {
        return $notification->delete();
    }

    /**
     * Get unread count.
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->unread()
            ->count();
    }

    /**
     * Notify course update.
     */
    public function notifyCourseUpdate(User $user, $course): void
    {
        $this->send(
            $user,
            'course_update',
            'Course Updated',
            "The course \"{$course->title}\" has been updated.",
            ['course_id' => $course->id]
        );
    }

    /**
     * Notify lab completion.
     */
    public function notifyLabCompletion(User $user, $lab): void
    {
        $this->send(
            $user,
            'lab_completed',
            'Lab Completed',
            "Congratulations! You completed the lab \"{$lab->title}\".",
            ['lab_id' => $lab->id]
        );
    }

    /**
     * Notify badge earned.
     */
    public function notifyBadgeEarned(User $user, $badge): void
    {
        $this->send(
            $user,
            'badge_earned',
            'Badge Earned',
            "You earned the badge \"{$badge->name}\"!",
            ['badge_id' => $badge->id]
        );
    }

    /**
     * Notify certificate issued.
     */
    public function notifyCertificateIssued(User $user, $certificate): void
    {
        $this->send(
            $user,
            'certificate_issued',
            'Certificate Issued',
            "Your certificate \"{$certificate->title}\" has been issued.",
            ['certificate_id' => $certificate->id]
        );
    }

    /**
     * Notify subscription expiring.
     */
    public function notifySubscriptionExpiring(User $user, $subscription): void
    {
        $this->send(
            $user,
            'subscription_expiring',
            'Subscription Expiring',
            "Your subscription will expire on {$subscription->current_period_end->format('M d, Y')}.",
            ['subscription_id' => $subscription->id],
            'high'
        );
    }
}
