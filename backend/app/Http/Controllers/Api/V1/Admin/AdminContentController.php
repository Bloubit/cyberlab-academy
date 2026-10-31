<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Level;
use App\Models\Quiz;
use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminContentController extends Controller
{
    // ============ BADGES ============

    /**
     * List all badges.
     */
    public function badges(Request $request): JsonResponse
    {
        try {
            $query = Badge::query();

            if ($request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                      ->orWhereRaw('LOWER(description) LIKE ?', ['%' . mb_strtolower($search) . '%']);
                });
            }

            if ($request->has('is_active')) {
                $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->category) {
                $query->where('category', $request->category);
            }

            $badges = $query->orderBy('sort_order')->paginate(50);

            $data = collect($badges->items())->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
                'description' => $b->description,
                'icon' => $b->icon,
                'category' => $b->category,
                'rarity' => $b->rarity,
                'xp_reward' => $b->xp_reward,
                'requirements' => $b->requirements,
                'is_active' => $b->is_active,
                'is_hidden' => $b->is_hidden,
                'earned_count' => $b->users()->count(),
                'created_at' => $b->created_at?->toISOString(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => $badges->total(),
                    'per_page' => $badges->perPage(),
                    'current_page' => $badges->currentPage(),
                    'last_page' => $badges->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch badges: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create badge.
     */
    public function storeBadge(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string|max:500',
                'icon' => 'nullable|string|max:100',
                'category' => 'nullable|string|max:50',
                'rarity' => 'nullable|in:common,uncommon,rare,epic,legendary',
                'xp_reward' => 'nullable|integer|min:0',
                'requirements' => 'nullable|array',
                'is_active' => 'boolean',
                'is_hidden' => 'boolean',
            ]);

            $validated['slug'] = Str::slug($validated['name']);
            $validated['sort_order'] = Badge::count() + 1;

            $badge = Badge::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Badge created successfully',
                'data' => $badge,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create badge: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update badge.
     */
    public function updateBadge(Request $request, int $id): JsonResponse
    {
        try {
            $badge = Badge::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:100',
                'description' => 'nullable|string|max:500',
                'icon' => 'nullable|string|max:100',
                'category' => 'nullable|string|max:50',
                'rarity' => 'nullable|in:common,uncommon,rare,epic,legendary',
                'xp_reward' => 'nullable|integer|min:0',
                'requirements' => 'nullable|array',
                'is_active' => 'boolean',
                'is_hidden' => 'boolean',
            ]);

            $badge->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Badge updated successfully',
                'data' => $badge->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update badge: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete badge.
     */
    public function destroyBadge(int $id): JsonResponse
    {
        try {
            $badge = Badge::findOrFail($id);

            // Detach all users first
            $badge->users()->detach();
            $badge->delete();

            return response()->json([
                'success' => true,
                'message' => 'Badge deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete badge: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============ QUIZZES ============

    /**
     * List all quizzes.
     */
    public function quizzes(Request $request): JsonResponse
    {
        try {
            $query = Quiz::with(['course:id,title']);

            if ($request->search) {
                $search = $request->search;
                $query->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($search) . '%']);
            }

            $perPage = min((int) ($request->per_page ?? 15), 100);
            $quizzes = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $quizzes,
                'meta' => [
                    'current_page' => $quizzes->currentPage(),
                    'last_page' => $quizzes->lastPage(),
                    'per_page' => $quizzes->perPage(),
                    'total' => $quizzes->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quizzes: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============ MODULES/LESSONS ============

    /**
     * Get modules for a course (with lessons).
     */
    public function modules(int $courseId): JsonResponse
    {
        try {
            $modules = Module::where('course_id', $courseId)
                ->with(['lessons' => fn($q) => $q->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get()
                ->map(fn($m) => [
                    'id' => $m->id,
                    'title' => $m->title,
                    'description' => $m->description,
                    'sort_order' => $m->sort_order,
                    'is_published' => $m->is_published,
                    'lessons_count' => $m->lessons->count(),
                    'total_duration' => $m->lessons->sum('duration_minutes'),
                    'lessons' => $m->lessons->map(fn($l) => [
                        'id' => $l->id,
                        'title' => $l->title,
                        'content_type' => $l->content_type,
                        'sort_order' => $l->sort_order,
                        'is_published' => $l->is_published,
                        'duration_minutes' => $l->duration_minutes,
                        'xp_reward' => $l->xp_reward,
                    ]),
                ]);

            return response()->json([
                'success' => true,
                'data' => $modules,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch modules: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update lesson.
     */
    public function updateLesson(Request $request, int $lessonId): JsonResponse
    {
        try {
            $lesson = Lesson::findOrFail($lessonId);

            $validated = $request->validate([
                'title' => 'sometimes|string|max:200',
                'content' => 'nullable|string',
                'content_type' => 'sometimes|in:text,video,quiz,lab,exam',
                'is_published' => 'boolean',
                'is_free' => 'boolean',
                'duration_minutes' => 'nullable|integer|min:1',
                'xp_reward' => 'nullable|integer|min:0',
            ]);

            $lesson->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Lesson updated successfully',
                'data' => $lesson->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lesson: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============ SYSTEM STATS ============

    /**
     * Get content statistics.
     */
    public function stats(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_courses' => \App\Models\Course::count(),
                    'total_modules' => Module::count(),
                    'total_lessons' => Lesson::count(),
                    'total_labs' => \App\Models\Lab::count(),
                    'total_challenges' => \App\Models\Challenge::count(),
                    'total_quizzes' => Quiz::count(),
                    'total_questions' => \App\Models\Question::count(),
                    'total_badges' => Badge::count(),
                    'total_certificates' => \App\Models\Certificate::count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stats: ' . $e->getMessage(),
            ], 500);
        }
    }
}
