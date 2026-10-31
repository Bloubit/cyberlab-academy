<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Lab;
use App\Models\Badge;
use App\Models\Quiz;
use App\Models\Submission;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Get admin dashboard statistics.
     */
    public function index(): JsonResponse
    {
        try {
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();
            $bannedUsers = User::where('is_banned', true)->count();
            $newUsersToday = User::whereDate('created_at', today())->count();

            $totalCourses = Course::count();
            $publishedCourses = Course::where('is_published', true)->count();
            $featuredCourses = Course::where('is_featured', true)->count();

            $totalLabs = Lab::count();
            $publishedLabs = Lab::where('is_published', true)->count();

            $totalBadges = Badge::count();
            $activeBadges = Badge::where('is_active', true)->count();

            $totalQuizzes = Quiz::count();
            $totalSubmissions = Submission::count();
            $correctSubmissions = Submission::where('is_correct', true)->count();

            $totalRevenue = Payment::where('status', 'completed')->sum('amount');
            $recentTransactions = Payment::with('user')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'user_name' => $p->user?->name ?? 'Unknown',
                    'amount' => $p->amount,
                    'currency' => $p->currency,
                    'status' => $p->status,
                    'created_at' => $p->created_at?->toISOString(),
                ]);

            // Recent registrations
            $recentUsers = User::latest()
                ->take(10)
                ->get()
                ->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'is_active' => $u->is_active,
                    'is_banned' => $u->is_banned,
                    'created_at' => $u->created_at?->toISOString(),
                ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'users' => [
                            'total' => $totalUsers,
                            'active' => $activeUsers,
                            'banned' => $bannedUsers,
                            'new_today' => $newUsersToday,
                        ],
                        'courses' => [
                            'total' => $totalCourses,
                            'published' => $publishedCourses,
                            'featured' => $featuredCourses,
                        ],
                        'labs' => [
                            'total' => $totalLabs,
                            'published' => $publishedLabs,
                        ],
                        'badges' => [
                            'total' => $totalBadges,
                            'active' => $activeBadges,
                        ],
                        'content' => [
                            'quizzes' => $totalQuizzes,
                            'submissions' => $totalSubmissions,
                            'correct_submissions' => $correctSubmissions,
                            'success_rate' => $totalSubmissions > 0
                                ? round(($correctSubmissions / $totalSubmissions) * 100, 1)
                                : 0,
                        ],
                        'revenue' => [
                            'total' => $totalRevenue,
                            'currency' => 'USD',
                        ],
                    ],
                    'recent_users' => $recentUsers,
                    'recent_transactions' => $recentTransactions,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
