<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LessonResource;
use App\Services\CourseService;
use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LessonController extends Controller
{
    public function __construct(
        private CourseService $courseService
    ) {}

    /**
     * Get lessons for module.
     */
    public function index(Module $module): AnonymousResourceCollection
    {
        $lessons = $module->lessons()
            ->with(['video', 'resources'])
            ->orderBy('sort_order')
            ->get();

        return LessonResource::collection($lessons);
    }

    /**
     * Get single lesson with access control.
     */
    public function show(Request $request, Lesson $lesson): JsonResponse
    {
        $lesson->load(['video', 'resources']);
        $user = $request->user();

        // Check if user can access this lesson
        if (!$lesson->is_free) {
            $canAccess = false;

            if ($user) {
                // Admin can access everything
                if ($user->isAdmin()) {
                    $canAccess = true;
                }
                // Enrolled users can access all lessons
                elseif ($lesson->module && $lesson->module->course) {
                    $canAccess = $lesson->module->course->isEnrolled($user);
                }
            }

            if (!$canAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'This lesson requires enrollment. Please enroll in the course to access this content.',
                    'data' => (new LessonResource($lesson))->toArray($request),
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => new LessonResource($lesson),
        ]);
    }

    /**
     * Store lesson.
     */
    public function store(Request $request, Module $module): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'content_type' => 'required|in:text,video,quiz,lab,exam',
            'content' => 'nullable|string',
            'is_free' => 'boolean',
            'duration_minutes' => 'nullable|integer|min:1',
            'xp_reward' => 'nullable|integer|min:0',
        ]);

        $lesson = $this->courseService->addLesson($module, $request->all());

        return response()->json([
            'message' => 'Lesson created successfully',
            'lesson' => new LessonResource($lesson),
        ], 201);
    }

    /**
     * Update lesson.
     */
    public function update(Request $request, Module $module, Lesson $lesson): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|string|max:200',
            'content' => 'nullable|string',
            'is_free' => 'boolean',
            'is_published' => 'boolean',
            'duration_minutes' => 'nullable|integer|min:1',
            'xp_reward' => 'nullable|integer|min:0',
        ]);

        $lesson->update($request->only([
            'title', 'content', 'is_free', 'is_published',
            'duration_minutes', 'xp_reward',
        ]));

        return response()->json([
            'message' => 'Lesson updated successfully',
            'lesson' => new LessonResource($lesson->fresh()),
        ]);
    }

    /**
     * Delete lesson.
     */
    public function destroy(Module $module, Lesson $lesson): JsonResponse
    {
        $lesson->delete();

        return response()->json([
            'message' => 'Lesson deleted successfully',
        ]);
    }

    /**
     * Complete lesson.
     */
    public function complete(Lesson $lesson, Request $request): JsonResponse
    {
        $progress = $lesson->progress()
            ->firstOrCreate(['user_id' => $request->user()->id]);

        $progress->markAsCompleted();

        // Award XP
        if ($lesson->xp_reward > 0) {
            \App\Models\XpHistory::award(
                $request->user(),
                $lesson->xp_reward,
                'lesson',
                $lesson->id,
                "Completed lesson: {$lesson->title}"
            );
        }

        return response()->json([
            'message' => 'Lesson completed successfully',
            'progress' => $progress,
        ]);
    }
}
