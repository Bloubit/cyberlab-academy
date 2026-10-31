<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Upload an image file and return the URL.
     */
    public function image(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'type' => 'nullable|string|in:avatar,thumbnail,general',
            ]);

            if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file upload',
                ], 400);
            }

            $file = $request->file('file');
            $type = $request->input('type', 'general');
            $userId = $request->user()?->id ?? 'anonymous';

            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = sprintf(
                '%s_%s_%s.%s',
                $type,
                $userId,
                Str::random(16),
                $extension
            );

            // Determine storage path
            $folder = match ($type) {
                'avatar' => 'uploads/avatars',
                'thumbnail' => 'uploads/thumbnails',
                default => 'uploads/general',
            };

            // Store the file
            $path = $file->storeAs($folder, $filename, 'public');

            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store file',
                ], 500);
            }

            $url = Storage::disk('public')->url($path);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'url' => $url,
                    'path' => $path,
                    'filename' => $filename,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload multiple images at once.
     */
    public function images(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'files' => 'required|array|max:5',
                'files.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'type' => 'nullable|string|in:avatar,thumbnail,general',
            ]);

            $type = $request->input('type', 'general');
            $userId = $request->user()?->id ?? 'anonymous';
            $uploaded = [];

            foreach ($request->file('files') as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = sprintf(
                    '%s_%s_%s.%s',
                    $type,
                    $userId,
                    Str::random(16),
                    $extension
                );

                $folder = match ($type) {
                    'avatar' => 'uploads/avatars',
                    'thumbnail' => 'uploads/thumbnails',
                    default => 'uploads/general',
                };

                $path = $file->storeAs($folder, $filename, 'public');

                if ($path) {
                    $uploaded[] = [
                        'url' => Storage::disk('public')->url($path),
                        'path' => $path,
                        'filename' => $filename,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($uploaded) . ' file(s) uploaded successfully',
                'data' => [
                    'files' => $uploaded,
                    'count' => count($uploaded),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an uploaded file.
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'path' => 'required|string',
            ]);

            $path = $request->input('path');

            if (!Storage::disk('public')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                ], 404);
            }

            Storage::disk('public')->delete($path);

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
