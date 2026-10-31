<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstanceResource;
use App\Models\Lab;
use App\Models\LabInstance;
use App\Models\Challenge;
use App\Models\Submission;
use App\Services\LabService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabInstanceController extends Controller
{
    public function __construct(
        private readonly LabService $labService
    ) {}

    /**
     * Start a lab instance for the authenticated user.
     *
     * POST /api/labs/{lab}/start
     */
    public function start(Request $request, Lab $lab): JsonResponse
    {
        try {
            // Check if user already has a running instance
            $existing = LabInstance::where('user_id', $request->user()->id)
                ->where('lab_id', $lab->id)
                ->where('status', 'running')
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'You already have a running instance for this lab',
                    'data' => new InstanceResource($existing),
                ]);
            }

            $instance = $this->labService->startInstance($request->user(), $lab);

            return response()->json([
                'success' => true,
                'message' => 'Lab environment started successfully',
                'data' => new InstanceResource($instance->load('lab')),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to start lab environment',
            ], 500);
        }
    }

    /**
     * Stop a running lab instance.
     *
     * POST /api/labs/{lab}/stop
     */
    public function stop(Request $request, Lab $lab): JsonResponse
    {
        try {
            $instance = LabInstance::where('user_id', $request->user()->id)
                ->where('lab_id', $lab->id)
                ->where('status', 'running')
                ->first();

            if (!$instance) {
                return response()->json([
                    'success' => false,
                    'message' => 'No running instance found for this lab',
                ], 404);
            }

            $this->labService->stopInstance($instance);

            return response()->json([
                'success' => true,
                'message' => 'Lab environment stopped successfully',
                'data' => ['status' => 'stopped'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to stop lab environment',
            ], 500);
        }
    }

    /**
     * Reset a running lab instance (refresh timer, reset state).
     *
     * POST /api/labs/{lab}/reset
     */
    public function reset(Request $request, Lab $lab): JsonResponse
    {
        try {
            $instance = LabInstance::where('user_id', $request->user()->id)
                ->where('lab_id', $lab->id)
                ->where('status', 'running')
                ->first();

            if (!$instance) {
                return response()->json([
                    'success' => false,
                    'message' => 'No running instance found for this lab',
                ], 404);
            }

            $instance = $this->labService->resetInstance($instance);

            return response()->json([
                'success' => true,
                'message' => 'Lab environment reset successfully',
                'data' => new InstanceResource($instance->load('lab')),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to reset lab environment',
            ], 500);
        }
    }

    /**
     * Get the current status of a lab instance.
     *
     * GET /api/labs/{lab}/instance
     */
    public function status(Request $request, Lab $lab): JsonResponse
    {
        try {
            $instance = LabInstance::where('user_id', $request->user()->id)
                ->where('lab_id', $lab->id)
                ->latest()
                ->first();

            if (!$instance) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'No instance found for this lab',
                ]);
            }

            // Auto-expire if past expiry
            if ($instance->status === 'running' && $instance->isExpired()) {
                $instance->update(['status' => 'expired']);
            }

            return response()->json([
                'success' => true,
                'data' => new InstanceResource($instance),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to fetch instance status',
            ], 500);
        }
    }

    /**
     * Get all lab instances for the authenticated user.
     *
     * GET /api/labs/mine
     */
    public function myLabs(Request $request): JsonResponse
    {
        try {
            $perPage = min((int) $request->input('per_page', 15), 50);

            $instances = LabInstance::with(['lab'])
                ->where('user_id', $request->user()->id)
                ->latest()
                ->paginate($perPage);

            $data = $instances->through(function ($instance) use ($request) {
                return new InstanceResource($instance);
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to fetch lab instances',
            ], 500);
        }
    }

    /**
     * Get details of a specific instance by ID.
     *
     * GET /api/instances/{instance}
     */
    public function show(Request $request, LabInstance $instance): JsonResponse
    {
        try {
            // Ensure user owns this instance or is admin
            if ($instance->user_id !== $request->user()->id && !$request->user()->hasRole('super-admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => new InstanceResource($instance->load('lab')),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to fetch instance details',
            ], 500);
        }
    }
}
