<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\LabController;
use App\Http\Controllers\Api\V1\LabInstanceController;
use App\Http\Controllers\Api\V1\QuizController;
use App\Http\Controllers\Api\V1\CertificateController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\UploadController;
use App\Http\Controllers\Api\V1\Payment\ClickController;
use App\Http\Controllers\Api\V1\Payment\PayzeController;
use App\Http\Controllers\Api\V1\Admin\AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\Admin\AdminCourseController;
use App\Http\Controllers\Api\V1\Admin\AdminLabController;
use App\Http\Controllers\Api\V1\Admin\AdminContentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| CyberLab Academy - Complete API
|
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
        'environment' => app()->environment(),
    ]);
});

// Root
Route::get('/', function () {
    return response()->json([
        'name' => 'CyberLab Academy API',
        'version' => '1.0.0',
        'status' => 'running',
        'documentation' => '/api/health',
    ]);
});

// Test endpoint
Route::get('/test', function () {
    return response()->json([
        'message' => 'CyberLab Academy API is working!',
        'status' => 'success',
        'timestamp' => now()->toISOString(),
    ]);
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Public verification & password reset
    Route::post('/email/verify', [AuthController::class, 'verifyEmail']);
    Route::post('/password/forgot', [AuthController::class, 'sendPasswordResetLink']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);

    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/email/verify/send', [AuthController::class, 'sendVerificationEmail']);
    });
});

/*
|--------------------------------------------------------------------------
| Public Course Routes (No Auth Required)
|--------------------------------------------------------------------------
*/
Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
    Route::get('/featured', [CourseController::class, 'featured']);
    Route::get('/enrolled', [CourseController::class, 'enrolled'])->middleware('auth:sanctum');
    Route::get('/{id}', [CourseController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Upload Routes (Require Authentication)
|--------------------------------------------------------------------------
*/
Route::prefix('uploads')->middleware('auth:sanctum')->group(function () {
    Route::post('/image', [UploadController::class, 'image']);
    Route::post('/images', [UploadController::class, 'images']);
    Route::delete('/delete', [UploadController::class, 'delete']);
});

/*
|--------------------------------------------------------------------------
| Protected API Routes (Require Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    | Course Routes (Protected)
    */
    Route::prefix('courses')->group(function () {
        Route::post('/{id}/enroll', [CourseController::class, 'enroll']);
        Route::get('/{id}/progress', [CourseController::class, 'progress']);
    });

    /*
    | Lab Routes
    */
    Route::prefix('labs')->group(function () {
        Route::get('/', [LabController::class, 'index']);
        Route::get('/{id}', [LabController::class, 'show']);

        // Instance management with implicit route-model binding
        Route::get('/{lab}/instance', [LabInstanceController::class, 'status']);
        Route::post('/{lab}/start', [LabInstanceController::class, 'start']);
        Route::post('/{lab}/stop', [LabInstanceController::class, 'stop']);
        Route::post('/{lab}/reset', [LabInstanceController::class, 'reset']);

        // Flag submission
        Route::post('/{labId}/challenges/{challengeId}/submit', [LabController::class, 'submitFlag']);
    });

    /*
    | My Lab Instances
    */
    Route::prefix('labs')->group(function () {
        Route::get('/mine', [LabInstanceController::class, 'myLabs']);
    });

    /*
    | Individual Instance Details
    */
    Route::get('/instances/{instance}', [LabInstanceController::class, 'show']);

    /*
    | Quiz Routes
    */
    Route::prefix('quizzes')->group(function () {
        Route::get('/', [QuizController::class, 'index']);
        Route::get('/{id}', [QuizController::class, 'show']);
        Route::post('/{id}/start', [QuizController::class, 'start']);
        Route::post('/{id}/submit', [QuizController::class, 'submit']);
        Route::get('/{id}/results/{attemptId}', [QuizController::class, 'results']);
    });

    /*
    | Certificate Routes
    */
    Route::prefix('certificates')->group(function () {
        Route::get('/', [CertificateController::class, 'index']);
        Route::get('/{id}', [CertificateController::class, 'show']);
        Route::get('/{id}/download', [CertificateController::class, 'download']);
    });

    /*
    | Payment Routes
    */
    Route::prefix('payments')->group(function () {
        Route::get('/plans', [PaymentController::class, 'plans']);
        Route::get('/subscriptions', [PaymentController::class, 'mySubscriptions']);
        Route::post('/subscriptions/{planId}/subscribe', [PaymentController::class, 'subscribe']);
        Route::post('/subscriptions/cancel', [PaymentController::class, 'cancelSubscription']);
        Route::get('/history', [PaymentController::class, 'payments']);
    });

    /*
    | Notification Routes
    */
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    /*
    | User Routes
    */
    Route::prefix('user')->group(function () {
        Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
            $user = $request->user();

            // Lab statistics
            $activeLabs = $user->labInstances()
                ->where('status', 'running')
                ->count();

            $totalLabInstances = $user->labInstances()->count();
            $completedLabs = $user->labInstances()
                ->where('status', 'completed')
                ->count();

            // XP earned from lab challenges (sum of points_earned from correct submissions)
            $labXpEarned = \App\Models\Submission::where('user_id', $user->id)
                ->where('is_correct', true)
                ->sum('points_earned');

            // Total challenges solved
            $challengesSolved = \App\Models\Submission::where('user_id', $user->id)
                ->where('is_correct', true)
                ->distinct('challenge_id')
                ->count('challenge_id');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'avatar' => $user->avatar,

                    // XP & Level
                    'total_xp' => $user->total_xp,
                    'level' => $user->level,

                    // Course stats
                    'enrolled_courses_count' => $user->enrolledCourses()->count(),
                    'completed_courses_count' => $user->enrolledCourses()
                        ->wherePivot('status', 'completed')
                        ->count(),

                    // Lab stats
                    'active_labs_count' => $activeLabs,
                    'total_lab_instances' => $totalLabInstances,
                    'completed_labs_count' => $completedLabs,
                    'lab_xp_earned' => $labXpEarned,
                    'challenges_solved' => $challengesSolved,

                    // Other
                    'badges_count' => $user->badges()->count(),
                    'certificates_count' => $user->certificates()->count(),
                ],
            ]);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Require Authentication + Admin Role)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth:sanctum', \App\Http\Middleware\AdminMiddleware::class])->group(function () {

    /*
    | Dashboard
    */
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    /*
    | Users
    */
    Route::prefix('users')->group(function () {
        Route::get('/', [AdminUserController::class, 'index']);
        Route::get('/roles', [AdminUserController::class, 'roles']);
        Route::get('/{id}', [AdminUserController::class, 'show']);
        Route::put('/{id}', [AdminUserController::class, 'update']);
        Route::post('/{id}/ban', [AdminUserController::class, 'ban']);
        Route::post('/{id}/unban', [AdminUserController::class, 'unban']);
        Route::post('/{id}/assign-role', [AdminUserController::class, 'assignRole']);
    });

    /*
    | Courses
    */
    Route::prefix('courses')->group(function () {
        Route::get('/', [AdminCourseController::class, 'index']);
        Route::post('/', [AdminCourseController::class, 'store']);
        Route::get('/{id}', [AdminCourseController::class, 'show']);
        Route::put('/{id}', [AdminCourseController::class, 'update']);
        Route::delete('/{id}', [AdminCourseController::class, 'destroy']);
        Route::post('/{id}/toggle-publish', [AdminCourseController::class, 'togglePublish']);

        // Categories
        Route::get('/categories/list', [AdminCourseController::class, 'categories']);
        Route::post('/categories', [AdminCourseController::class, 'storeCategory']);
        Route::put('/categories/{id}', [AdminCourseController::class, 'updateCategory']);
        Route::delete('/categories/{id}', [AdminCourseController::class, 'destroyCategory']);
    });

    /*
    | Labs
    */
    Route::prefix('labs')->group(function () {
        Route::get('/', [AdminLabController::class, 'index']);
        Route::post('/', [AdminLabController::class, 'store']);
        Route::get('/{id}', [AdminLabController::class, 'show']);
        Route::put('/{id}', [AdminLabController::class, 'update']);
        Route::delete('/{id}', [AdminLabController::class, 'destroy']);
        Route::post('/{id}/toggle-publish', [AdminLabController::class, 'togglePublish']);

        // Challenges
        Route::get('/{labId}/challenges', [AdminLabController::class, 'challenges']);
        Route::post('/{labId}/challenges', [AdminLabController::class, 'storeChallenge']);
    });

    /*
    | Lab Instances Monitoring
    */
    Route::prefix('instances')->group(function () {
        Route::get('/', function (\Illuminate\Http\Request $request) {
            try {
                $query = \App\Models\LabInstance::with(['lab', 'user']);

                if ($request->status) {
                    $query->where('status', $request->status);
                }

                if ($request->lab_id) {
                    $query->where('lab_id', $request->lab_id);
                }

                if ($request->user_id) {
                    $query->where('user_id', $request->user_id);
                }

                $sortField = $request->sort_by ?? 'created_at';
                $sortOrder = $request->sort_order ?? 'desc';
                $perPage = min((int) ($request->per_page ?? 20), 100);

                $instances = $query->orderBy($sortField, $sortOrder)->paginate($perPage);

                $data = $instances->through(function ($instance) {
                    return [
                        'id' => $instance->id,
                        'lab_id' => $instance->lab_id,
                        'user_id' => $instance->user_id,
                        'status' => $instance->status,
                        'started_at' => $instance->started_at?->toISOString(),
                        'stopped_at' => $instance->stopped_at?->toISOString(),
                        'expires_at' => $instance->expires_at?->toISOString(),
                        'connection_info' => $instance->connection_info,
                        'created_at' => $instance->created_at?->toISOString(),
                        'lab' => $instance->lab ? [
                            'id' => $instance->lab->id,
                            'title' => $instance->lab->title,
                            'difficulty_level' => $instance->lab->difficulty_level,
                            'type' => $instance->lab->type,
                        ] : null,
                        'user' => $instance->user ? [
                            'id' => $instance->user->id,
                            'name' => $instance->user->name,
                            'email' => $instance->user->email,
                        ] : null,
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'meta' => [
                        'current_page' => $instances->currentPage(),
                        'last_page' => $instances->lastPage(),
                        'per_page' => $instances->perPage(),
                        'total' => $instances->total(),
                    ],
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch instances: ' . $e->getMessage(),
                ], 500);
            }
        });
        Route::post('/{instance}/stop', function (\Illuminate\Http\Request $request, \App\Models\LabInstance $instance) {
            try {
                $instance->update([
                    'status' => 'stopped',
                    'stopped_at' => now(),
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Instance stopped successfully',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to stop instance: ' . $e->getMessage(),
                ], 500);
            }
        });
    });

    /*
    | Content Management
    */
    Route::prefix('content')->group(function () {
        // Badges
        Route::get('/badges', [AdminContentController::class, 'badges']);
        Route::post('/badges', [AdminContentController::class, 'storeBadge']);
        Route::put('/badges/{id}', [AdminContentController::class, 'updateBadge']);
        Route::delete('/badges/{id}', [AdminContentController::class, 'destroyBadge']);

        // Quizzes
        Route::get('/quizzes', [AdminContentController::class, 'quizzes']);

        // Modules/Lessons
        Route::get('/courses/{courseId}/modules', [AdminContentController::class, 'modules']);
        Route::put('/lessons/{lessonId}', [AdminContentController::class, 'updateLesson']);

        // Stats
        Route::get('/stats', [AdminContentController::class, 'stats']);
    });
});

/*
|--------------------------------------------------------------------------
| Public Leaderboard (No Auth Required)
|--------------------------------------------------------------------------
*/
Route::get('/leaderboard', function (\Illuminate\Http\Request $request) {
    try {
        $period = $request->input('period', 'all_time');
        $limit = min((int) $request->input('limit', 50), 100);

        $users = \App\Models\User::where('is_active', true)
            ->with('xpSummary')
            ->get()
            ->filter(fn($u) => $u->xpSummary)
            ->sortByDesc(fn($u) => $u->xpSummary->total_xp)
            ->values()
            ->take($limit);

        // Note: For a full implementation, the 'monthly' and 'weekly' filters
        // would query XpHistory within the date range. For now, all periods
        // return total XP as the primary ranking metric.

        $leaders = $users->map(fn($u, $idx) => [
            'id' => $u->id,
            'rank' => $idx + 1,
            'name' => $u->name,
            'username' => $u->username,
            'email' => $u->email,
            'avatar' => $u->avatar_url,
            'level' => $u->xpSummary->level ?? 1,
            'xp' => $u->xpSummary->total_xp ?? 0,
            'streak' => $u->xpSummary->current_streak ?? 0,
            'created_at' => $u->created_at?->toISOString(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $leaders,
            'meta' => [
                'total' => $leaders->count(),
                'period' => $period,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch leaderboard',
        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| Public Routes - Home Page Data (No Auth Required)
|--------------------------------------------------------------------------
*/
Route::get('/home', function () {
    try {
        $featuredCourses = \App\Models\Course::published()
            ->featured()
            ->with('category')
            ->take(6)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'slug' => $c->slug,
                'description' => $c->description,
                'difficulty_level' => $c->difficulty_level,
                'price' => (float) $c->price,
                'is_free' => $c->is_free,
                'currency' => $c->currency ?? 'USD',
                'thumbnail' => $c->thumbnail,
                'icon' => $c->icon ?? '📚',
                'enrollment_count' => $c->enrollment_count,
                'total_lessons' => $c->total_lessons,
                'estimated_hours' => $c->estimated_hours,
                'category' => $c->category?->name,
                'created_at' => $c->created_at?->toISOString(),
            ]);

        $publishedLabs = \App\Models\Lab::published()
            ->take(6)
            ->get()
            ->map(fn($l) => [
                'id' => $l->id,
                'title' => $l->title,
                'slug' => $l->slug,
                'description' => $l->description,
                'difficulty_level' => $l->difficulty_level,
                'type' => $l->type,
                'xp_reward' => $l->xp_reward,
                'duration_minutes' => $l->duration_minutes,
                'created_at' => $l->created_at?->toISOString(),
            ]);

        $totalUsers = \App\Models\User::count();
        $totalCourses = \App\Models\Course::published()->count();
        $totalLabs = \App\Models\Lab::published()->count();
        $completedLabs = \App\Models\LabInstance::where('status', 'completed')->count();
        $activeStudents = \App\Models\User::where('is_active', true)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'featured_courses' => $featuredCourses,
                'labs' => $publishedLabs,
                'stats' => [
                    'total_users' => $totalUsers,
                    'active_students' => $activeStudents,
                    'total_courses' => $totalCourses,
                    'total_labs' => $totalLabs,
                    'labs_completed' => $completedLabs,
                ],
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch home data',
        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| Payment Gateway Webhooks (No Auth Required - Signed Requests)
|--------------------------------------------------------------------------
*/
Route::prefix('payments')->group(function () {
    // Click.uz webhooks
    Route::prefix('click')->group(function () {
        Route::post('/prepare', [ClickController::class, 'prepare']);
        Route::post('/complete', [ClickController::class, 'complete']);
        Route::post('/callback', [ClickController::class, 'callback']);
        Route::get('/return', [ClickController::class, 'returnUrl']);
    });

    // Payze.io webhooks
    Route::prefix('payze')->group(function () {
        Route::post('/hook', [PayzeController::class, 'hook']);
        Route::get('/success', [PayzeController::class, 'success']);
        Route::get('/error', [PayzeController::class, 'error']);
        Route::post('/refund/{paymentId}', [PayzeController::class, 'refund']);
    });
});

/*
|--------------------------------------------------------------------------
| Public Certificate Verification (No Auth Required)
|--------------------------------------------------------------------------
*/
Route::get('/certificates/verify/{number}', [CertificateController::class, 'verify']);
