<?php

namespace App\Services;

use App\Mail\PasswordReset;
use App\Mail\VerifyEmail;
use App\Models\User;
use App\Models\UserXpSummary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Register a new user.
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'] ?? Str::slug($data['name']) . '-' . Str::random(5),
            'password' => Hash::make($data['password']),
            'email_verified_at' => null,
        ]);

        // Create XP summary
        UserXpSummary::create(['user_id' => $user->id]);

        // Assign default role (student)
        $user->assignRole('student');

        // Generate email verification token
        $token = Str::random(60);
        $user->update([
            'settings' => array_merge($user->settings ?? [], [
                'email_verification_token' => $token,
            ]),
        ]);

        // Send verification email
        try {
            $verificationUrl = url("/verify-email?token={$token}");
            Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));
        } catch (\Exception $e) {
            // Log email error but don't block registration
            logger()->error('Failed to send verification email: ' . $e->getMessage());
        }

        // Create token for API
        $tokenResult = $user->createToken('auth-token');
        $plainTextToken = $tokenResult->plainTextToken;

        return [
            'user' => $user,
            'token' => $plainTextToken,
        ];
    }

    /**
     * Login user.
     */
    public function login(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if (!$user->is_active) {
            return null;
        }

        if ($user->is_banned) {
            return null;
        }

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
            'login_attempts' => 0,
        ]);

        // Create token
        $tokenResult = $user->createToken('auth-token');

        return [
            'user' => $user,
            'token' => $tokenResult->plainTextToken,
        ];
    }

    /**
     * Logout user.
     */
    public function logout(User $user): bool
    {
        $user->tokens()->delete();
        return true;
    }

    /**
     * Refresh token.
     */
    public function refresh(User $user): array
    {
        $user->tokens()->delete();
        $tokenResult = $user->createToken('auth-token');

        return [
            'token' => $tokenResult->plainTextToken,
        ];
    }

    /**
     * Verify email.
     */
    public function verifyEmail(string $token): bool
    {
        $user = User::where('settings->email_verification_token', $token)->first();

        if (!$user) {
            return false;
        }

        $user->update([
            'email_verified_at' => now(),
            'settings' => array_merge($user->settings ?? [], [
                'email_verification_token' => null,
            ]),
        ]);

        return true;
    }

    /**
     * Send password reset email.
     */
    public function sendPasswordReset(string $email): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        $token = Str::random(60);
        $user->update([
            'settings' => array_merge($user->settings ?? [], [
                'password_reset_token' => $token,
                'password_reset_expires' => now()->addHours(2)->timestamp,
            ]),
        ]);

        // Send password reset email
        try {
            $resetUrl = url("/reset-password?token={$token}&email=" . urlencode($user->email));
            Mail::to($user->email)->send(new PasswordReset($user, $resetUrl));
        } catch (\Exception $e) {
            // Log email error but don't block the reset flow
            logger()->error('Failed to send password reset email: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Reset password.
     */
    public function resetPassword(string $token, string $password): bool
    {
        $user = User::where('settings->password_reset_token', $token)->first();

        if (!$user) {
            return false;
        }

        $expiresAt = data_get($user->settings, 'password_reset_expires');
        if ($expiresAt && now()->timestamp > $expiresAt) {
            return false;
        }

        $user->update([
            'password' => Hash::make($password),
            'settings' => array_merge($user->settings ?? [], [
                'password_reset_token' => null,
                'password_reset_expires' => null,
            ]),
        ]);

        $user->tokens()->delete();

        return true;
    }
}
