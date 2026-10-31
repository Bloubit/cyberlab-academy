<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LabResource;
use App\Models\Lab;
use App\Models\Challenge;
use App\Models\LabInstance;
use App\Models\Submission;
use App\Services\LabService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabController extends Controller
{
    public function __construct(
        private readonly LabService $labService
    ) {}

    /**
     * List all published labs
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $labs = Lab::with(['challenges'])
                ->when($request->difficulty_level, fn($q, $v) => $q->where('difficulty_level', $v))
                ->when($request->type, fn($q, $v) => $q->where('type', $v))
                ->when($request->course_id, fn($q, $v) => $q->where('course_id', $v))
                ->when($request->search, fn($q, $v) => $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($v) . '%']))
                ->paginate($request->input('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => LabResource::collection($labs),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch labs',
            ], 500);
        }
    }

    /**
     * Get single lab
     */
    public function show($id): JsonResponse
    {
        try {
            $lab = Lab::with(['challenges', 'machines'])->findOrFail($id);

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
                'message' => 'Failed to fetch lab',
            ], 500);
        }
    }

    /**
     * Start a lab instance
     */
    public function start(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $lab = Lab::findOrFail($id);
            $instance = $this->labService->startInstance($user, $lab);

            return response()->json([
                'success' => true,
                'message' => 'Lab started',
                'data' => $instance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to start lab',
            ], 500);
        }
    }

    /**
     * Stop a lab instance
     */
    public function stop(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $lab = Lab::findOrFail($id);
            $instance = LabInstance::where('user_id', $user->id)
                ->where('lab_id', $lab->id)
                ->where('status', 'running')
                ->first();

            if (!$instance) {
                return response()->json([
                    'success' => false,
                    'message' => 'No running instance found',
                ], 404);
            }

            $this->labService->stopInstance($instance);

            return response()->json([
                'success' => true,
                'message' => 'Lab stopped',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to stop lab',
            ], 500);
        }
    }

    /**
     * Submit flag for challenge
     */
    public function submitFlag(Request $request, $labId, $challengeId): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $request->validate([
                'flag' => 'required|string|max:255',
            ]);

            $challenge = Challenge::where('lab_id', $labId)
                ->findOrFail($challengeId);

            $submission = $this->labService->submitFlag(
                $user,
                $challenge,
                $request->input('flag')
            );

            return response()->json([
                'success' => $submission->is_correct,
                'message' => $submission->is_correct ? 'Correct flag!' : 'Incorrect flag',
                'data' => $submission,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Challenge not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to submit flag',
            ], 500);
        }
    }

    /**
     * Get user's active lab instances
     */
    public function myLabs(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $instances = LabInstance::with(['lab'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(15);

            // Attach solved challenge IDs for each instance
            $data = $instances->through(function ($instance) use ($user) {
                $solvedChallenges = Submission::where('user_id', $user->id)
                    ->where('is_correct', true)
                    ->whereIn('challenge_id', function ($q) use ($instance) {
                        $q->select('id')->from('challenges')->where('lab_id', $instance->lab_id);
                    })
                    ->pluck('challenge_id')
                    ->toArray();

                $instance->solved_challenges = $solvedChallenges;
                return $instance;
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lab instances',
            ], 500);
        }
    }

    /**
     * Reset a lab instance
     */
    public function reset(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $lab = Lab::findOrFail($id);
            $instance = LabInstance::where('user_id', $user->id)
                ->where('lab_id', $lab->id)
                ->where('status', 'running')
                ->first();

            if (!$instance) {
                return response()->json([
                    'success' => false,
                    'message' => 'No running instance found',
                ], 404);
            }

            $instance = $this->labService->resetInstance($instance);

            return response()->json([
                'success' => true,
                'message' => 'Lab environment reset successfully',
                'data' => $instance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to reset lab',
            ], 500);
        }
    }
}
