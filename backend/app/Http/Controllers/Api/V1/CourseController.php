<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(
        private readonly CourseService $courseService
    ) {}

    /**
     * Get all published courses
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['category_id', 'difficulty', 'search', 'is_free']);
            $perPage = $request->input('per_page', 15);

            $courses = Course::published()
                ->when($filters['category_id'] ?? null, fn($q, $v) => $q->byCategory($v))
                ->when($filters['difficulty'] ?? null, fn($q, $v) => $q->byDifficulty($v))
                ->when($filters['search'] ?? null, fn($q, $v) => $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($v) . '%']))
                ->when($filters['is_free'] ?? null, fn($q, $v) => $q->where('is_free', $v))
                ->with(['category'])
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => CourseResource::collection($courses),
                'meta' => [
                    'current_page' => $courses->currentPage(),
                    'last_page' => $courses->lastPage(),
                    'per_page' => $courses->perPage(),
                    'total' => $courses->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch courses',
            ], 500);
        }
    }

    /**
     * Get featured courses
     */
    public function featured(): JsonResponse
    {
        try {
            $courses = Course::published()
                ->featured()
                ->with(['category'])
                ->take(6)
                ->get();

            return response()->json([
                'success' => true,
                'data' => CourseResource::collection($courses),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch featured courses',
            ], 500);
        }
    }

    /**
     * Get single course by ID
     */
    public function show($id): JsonResponse
    {
        try {
            $course = Course::with(['category', 'instructor', 'modules', 'labs'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new CourseResource($course),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch course',
                'debug' => get_class($e) . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's enrolled courses
     */
    public function enrolled(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $enrolledCourses = $this->courseService->getEnrolledCourses($user);

            return response()->json([
                'success' => true,
                'data' => CourseResource::collection($enrolledCourses),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch enrolled courses',
            ], 500);
        }
    }

    /**
     * Enroll in a course
     */
    public function enroll(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $course = Course::findOrFail($id);

            if ($course->isEnrolled($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already enrolled in this course',
                ], 409);
            }

            $this->courseService->enroll($user, $course);

            return response()->json([
                'success' => true,
                'message' => 'Successfully enrolled in course',
                'data' => [
                    'course_id' => $course->id,
                    'title' => $course->title,
                ],
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Enrollment failed',
            ], 500);
        }
    }

    /**
     * Get course progress for current user
     */
    public function progress(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $course = Course::findOrFail($id);
            $progress = $this->courseService->getProgress($user, $course);

            if (!$progress['enrolled']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enrolled in this course',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $progress,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch progress',
            ], 500);
        }
    }
}
