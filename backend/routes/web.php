<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});

// Root URL
Route::get('/', function () {
    return response()->json([
        'name' => 'CyberLab Academy',
        'version' => '1.0.0',
        'status' => 'running',
        'endpoints' => [
            'health' => '/api/health',
            'test' => '/api/test',
            'auth' => '/api/v1/auth',
            'courses' => '/api/v1/courses',
            'labs' => '/api/v1/labs',
        ],
    ]);
});

// API Routes
Route::prefix('api')->group(function () {
    // Test endpoint
    Route::get('/test', function () {
        return response()->json([
            'message' => 'CyberLab Academy API is working!',
            'status' => 'success',
        ]);
    });

    // Protected routes (example)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});
