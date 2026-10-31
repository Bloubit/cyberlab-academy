<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function __construct(
        private readonly CertificateService $certificateService
    ) {}

    /**
     * List user's certificates
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $certificates = Certificate::where('user_id', $user->id)
                ->with(['course'])
                ->latest()
                ->paginate($request->input('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => CertificateResource::collection($certificates),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch certificates',
            ], 500);
        }
    }

    /**
     * Get single certificate
     */
    public function show($id): JsonResponse
    {
        try {
            $certificate = Certificate::with(['course', 'user'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new CertificateResource($certificate),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch certificate',
            ], 500);
        }
    }

    /**
     * Verify certificate by number
     */
    public function verify($number): JsonResponse
    {
        try {
            $result = $this->certificateService->verify($number);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Certificate not found or invalid',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Certificate is valid',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed',
            ], 500);
        }
    }

    /**
     * Download certificate as PDF
     */
    public function download($id): JsonResponse
    {
        try {
            $certificate = Certificate::findOrFail($id);

            // In production, this would generate and return a PDF
            // For now, return the certificate data
            return response()->json([
                'success' => true,
                'data' => new CertificateResource($certificate),
                'message' => 'PDF download endpoint - requires PDF library integration',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Download failed',
            ], 500);
        }
    }
}
