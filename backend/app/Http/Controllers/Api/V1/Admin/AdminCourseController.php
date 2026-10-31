<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCourseController extends Controller
{
    /**
     * List all courses (including unpublished).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Course::with(['category', 'instructor']);

            if ($request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                      ->orWhereRaw('LOWER(description) LIKE ?', ['%' . mb_strtolower($search) . '%']);
                });
            }

            if ($request->has('is_published')) {
                $query->where('is_published', filter_var($request->is_published, FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->category_id) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->difficulty) {
                $query->where('difficulty_level', $request->difficulty);
            }

            $sortField = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';
            $perPage = min((int) ($request->per_page ?? 15), 100);

            $courses = $query->orderBy($sortField, $sortOrder)
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
                'message' => 'Failed to fetch courses: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show single course with all details.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $course = Course::with([
                'category', 'instructor', 'modules.lessons',
                'enrollments.user', 'labs',
            ])->findOrFail($id);

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
                'message' => 'Failed to fetch course: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new course.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:200',
                'category_id' => 'nullable|exists:course_categories,id',
                'description' => 'nullable|string',
                'difficulty_level' => 'nullable|in:beginner,intermediate,advanced,expert',
                'estimated_hours' => 'nullable|numeric|min:0',
                'price' => 'nullable|numeric|min:0',
                'is_free' => 'boolean',
                'is_published' => 'boolean',
                'is_featured' => 'boolean',
                'thumbnail' => 'nullable|string|max:500',
                'requirements' => 'nullable|array',
                'learning_outcomes' => 'nullable|array',
                'tags' => 'nullable|array',
            ]);

            $validated['slug'] = Course::generateSlug($validated['title']);
            $validated['instructor_id'] = $request->user()->id;

            $course = Course::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Course created successfully',
                'data' => new CourseResource($course),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create course: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update course.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $course = Course::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|string|max:200',
                'category_id' => 'nullable|exists:course_categories,id',
                'description' => 'nullable|string',
                'difficulty_level' => 'nullable|in:beginner,intermediate,advanced,expert',
                'estimated_hours' => 'nullable|numeric|min:0',
                'price' => 'nullable|numeric|min:0',
                'is_free' => 'boolean',
                'is_published' => 'boolean',
                'is_featured' => 'boolean',
                'thumbnail' => 'nullable|string|max:500',
                'requirements' => 'nullable|array',
                'learning_outcomes' => 'nullable|array',
                'tags' => 'nullable|array',
                'subtitle' => 'nullable|string|max:300',
            ]);

            if (isset($validated['title']) && $validated['title'] !== $course->title) {
                $validated['slug'] = Course::generateSlug($validated['title']);
            }

            $course->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Course updated successfully',
                'data' => new CourseResource($course->fresh()),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update course: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete course (soft delete).
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $course = Course::findOrFail($id);
            $course->delete();

            return response()->json([
                'success' => true,
                'message' => 'Course deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete course: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle publish status.
     */
    public function togglePublish(int $id): JsonResponse
    {
        try {
            $course = Course::findOrFail($id);
            $course->update(['is_published' => !$course->is_published]);

            return response()->json([
                'success' => true,
                'message' => $course->is_published ? 'Course published' : 'Course unpublished',
                'data' => ['is_published' => $course->is_published],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle publish: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get course categories.
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = CourseCategory::orderBy('sort_order')->get();

            return response()->json([
                'success' => true,
                'data' => $categories->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'description' => $c->description,
                    'icon' => $c->icon,
                    'color' => $c->color,
                    'is_active' => $c->is_active,
                    'courses_count' => $c->courses()->count(),
                ]),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create category.
     */
    public function storeCategory(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string|max:500',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:20',
                'is_active' => 'boolean',
            ]);

            $validated['slug'] = Str::slug($validated['name']);
            $validated['sort_order'] = CourseCategory::count() + 1;

            $category = CourseCategory::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update category.
     */
    public function updateCategory(Request $request, int $id): JsonResponse
    {
        try {
            $category = CourseCategory::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:100',
                'description' => 'nullable|string|max:500',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:20',
                'is_active' => 'boolean',
            ]);

            $category->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete category.
     */
    public function destroyCategory(int $id): JsonResponse
    {
        try {
            $category = CourseCategory::findOrFail($id);

            if ($category->courses()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with existing courses',
                ], 409);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category: ' . $e->getMessage(),
            ], 500);
        }
    }
}
