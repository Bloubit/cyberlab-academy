<?php

namespace App\Http\Controllers\Api\V1;

use App\Mail\VerifyEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Register new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . ($e->getMessage() ?: 'Unknown error'),
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->email,
                $request->password
            );

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials or account is inactive/banned',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . ($e->getMessage() ?: 'Unknown error'),
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if ($user) {
                $this->authService->logout($user);
            }

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validate([
                'name' => 'sometimes|string|max:100',
                'bio' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:20',
                'country_code' => 'nullable|string|max:10',
                'timezone' => 'nullable|string|max:50',
                'language' => 'nullable|string|max:10',
                'avatar' => 'nullable|string|max:1000',
            ]);

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated',
                'data' => new UserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed',
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                ], 403);
            }

            $user->update([
                'password' => Hash::make($validated['new_password']),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to change password',
            ], 500);
        }
    }

    /**
     * Send email verification link
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->email_verified_at) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email already verified',
                    'data' => ['already_verified' => true],
                ]);
            }

            $token = Str::random(60);
            $user->update([
                'settings' => array_merge($user->settings ?? [], [
                    'email_verification_token' => $token,
                ]),
            ]);

            $verificationUrl = url("/verify-email?token={$token}");

            // Send the actual email
            try {
                Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));
            } catch (\Exception $mailEx) {
                logger()->error('Failed to send verification email: ' . $mailEx->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Verification email sent',
                'data' => [
                    'verification_url' => $verificationUrl,
                    'token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email',
            ], 500);
        }
    }

    /**
     * Verify email with token
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'token' => 'required|string',
            ]);

            $result = $this->authService->verifyEmail($request->token);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification token',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $result = $this->authService->sendPasswordReset($request->email);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with that email address',
                ], 404);
            }

            // In development: return the token directly so the user can use it
            $user = User::where('email', $request->email)->first();
            $token = data_get($user->settings, 'password_reset_token');
            $resetUrl = url("/reset-password?token={$token}&email={$request->email}");

            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email',
                'data' => [
                    'reset_url' => $resetUrl,
                    'token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reset link',
            ], 500);
        }
    }

    /**
     * Reset password with token
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Verify email matches the token
            $user = User::where('settings->password_reset_token', $validated['token'])->first();

            if (!$user || $user->email !== $validated['email']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token or email mismatch',
                ], 400);
            }

            $result = $this->authService->resetPassword(
                $validated['token'],
                $validated['password']
            );

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired reset token',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password reset failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
