<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModuleResource;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * Get modules for course.
     */
    public function index(Course $course): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $modules = $course->modules()
            ->with('lessons')
            ->orderBy('sort_order')
            ->get();

        return ModuleResource::collection($modules);
    }

    /**
     * Store module.
     */
    public function store(Request $request, Course $course): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
        ]);

        $module = $course->modules()->create([
            'title' => $request->title,
            'description' => $request->description,
            'sort_order' => $course->modules()->count() + 1,
        ]);

        return response()->json([
            'message' => 'Module created successfully',
            'module' => new ModuleResource($module),
        ], 201);
    }

    /**
     * Update module.
     */
    public function update(Request $request, Course $course, Module $module): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $module->update($request->only(['title', 'description', 'sort_order']));

        return response()->json([
            'message' => 'Module updated successfully',
            'module' => new ModuleResource($module->fresh()),
        ]);
    }

    /**
     * Delete module.
     */
    public function destroy(Course $course, Module $module): JsonResponse
    {
        $module->delete();

        return response()->json([
            'message' => 'Module deleted successfully',
        ]);
    }
}
