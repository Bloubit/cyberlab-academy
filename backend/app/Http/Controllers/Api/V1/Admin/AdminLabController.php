<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\LabResource;
use App\Models\Lab;
use App\Models\Challenge;
use App\Models\Flag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminLabController extends Controller
{
    /**
     * List all labs (including unpublished).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Lab::with(['course']);

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

            if ($request->difficulty_level) {
                $query->where('difficulty_level', $request->difficulty_level);
            }

            if ($request->type) {
                $query->where('type', $request->type);
            }

            if ($request->course_id) {
                $query->where('course_id', $request->course_id);
            }

            $sortField = in_array($request->sort_by, ['title', 'difficulty_level', 'type', 'xp_reward', 'created_at', 'updated_at'])
                ? $request->sort_by : 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';
            $perPage = min((int) ($request->per_page ?? 15), 100);

            $labs = $query->orderBy($sortField, $sortOrder)
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => LabResource::collection($labs),
                'meta' => [
                    'current_page' => $labs->currentPage(),
                    'last_page' => $labs->lastPage(),
                    'per_page' => $labs->perPage(),
                    'total' => $labs->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch labs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show single lab with details.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $lab = Lab::with(['course', 'challenges', 'machines'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new LabResource($lab),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lab not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lab: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new lab.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:200',
                'description' => 'nullable|string',
                'difficulty_level' => 'nullable|in:beginner,intermediate,advanced,expert',
                'type' => 'nullable|string|max:50',
                'duration_minutes' => 'nullable|integer|min:1',
                'xp_reward' => 'nullable|integer|min:0',
                'is_published' => 'boolean',
                'course_id' => 'nullable|exists:courses,id',
                'learning_objectives' => 'nullable|string',
                'hints' => 'nullable|string',
                'network_topology' => 'nullable|string',
            ]);

            $validated['slug'] = Str::slug($validated['title']);

            $lab = Lab::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Lab created successfully',
                'data' => new LabResource($lab),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create lab: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update lab.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $lab = Lab::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|string|max:200',
                'description' => 'nullable|string',
                'difficulty_level' => 'nullable|in:beginner,intermediate,advanced,expert',
                'type' => 'nullable|string|max:50',
                'duration_minutes' => 'nullable|integer|min:1',
                'xp_reward' => 'nullable|integer|min:0',
                'is_published' => 'boolean',
                'course_id' => 'nullable|exists:courses,id',
                'learning_objectives' => 'nullable|string',
                'hints' => 'nullable|string',
                'network_topology' => 'nullable|string',
                'settings' => 'nullable|array',
            ]);

            $lab->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Lab updated successfully',
                'data' => new LabResource($lab->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lab: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete lab (soft delete).
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $lab = Lab::findOrFail($id);
            $lab->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lab deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lab: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle publish status.
     */
    public function togglePublish(int $id): JsonResponse
    {
        try {
            $lab = Lab::findOrFail($id);
            $lab->update(['is_published' => !$lab->is_published]);

            return response()->json([
                'success' => true,
                'message' => $lab->is_published ? 'Lab published' : 'Lab unpublished',
                'data' => ['is_published' => $lab->is_published],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle publish: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get challenges for a lab.
     */
    public function challenges(int $labId): JsonResponse
    {
        try {
            $lab = Lab::findOrFail($labId);
            $challenges = $lab->challenges()->orderBy('sort_order')->get();

            return response()->json([
                'success' => true,
                'data' => $challenges,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch challenges: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create challenge for lab.
     */
    public function storeChallenge(Request $request, int $labId): JsonResponse
    {
        try {
            $lab = Lab::findOrFail($labId);

            $validated = $request->validate([
                'title' => 'required|string|max:200',
                'description' => 'nullable|string',
                'hint' => 'nullable|string',
                'points' => 'nullable|integer|min:0',
                'sort_order' => 'nullable|integer|min:0',
                'flag' => 'nullable|string|max:255',
            ]);

            if (!isset($validated['sort_order'])) {
                $validated['sort_order'] = $lab->challenges()->count() + 1;
            }

            $challenge = $lab->challenges()->create($validated);

            // Create flag if provided
            if (!empty($validated['flag'])) {
                Flag::createFlag($challenge, $validated['flag']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Challenge created successfully',
                'data' => $challenge->load('flags'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create challenge: ' . $e->getMessage(),
            ], 500);
        }
    }
}
