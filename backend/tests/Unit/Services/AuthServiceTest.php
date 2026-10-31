<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\UserXpSummary;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
        $this->authService = app(AuthService::class);
    }

    /** @test */
    public function it_can_register_a_new_user()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ];

        $result = $this->authService->register($data);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertEquals('test@example.com', $result['user']->email);
        $this->assertTrue(Hash::check('Password123!', $result['user']->password));
        $this->assertTrue($result['user']->hasRole('student'));

        // Verify XP summary was created
        $this->assertDatabaseHas('user_xp_summary', [
            'user_id' => $result['user']->id,
        ]);
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => 'Password123!',
            'is_active' => true,
        ]);

        $result = $this->authService->login($user->email, 'Password123!');

        $this->assertNotNull($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($user->email, $result['user']->email);
        $this->assertNotNull($result['user']->last_login_at);
    }

    /** @test */
    public function it_returns_null_for_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
        ]);

        $result = $this->authService->login($user->email, 'WrongPassword');

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_for_banned_user()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
            'is_active' => true,
            'is_banned' => true,
        ]);

        $result = $this->authService->login($user->email, 'Password123!');

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_for_inactive_user()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
            'is_active' => false,
        ]);

        $result = $this->authService->login($user->email, 'Password123!');

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_logout_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token');

        $result = $this->authService->logout($user);

        $this->assertTrue($result);
        $this->assertCount(0, $user->tokens);
    }

    /** @test */
    public function it_can_verify_email_with_valid_token()
    {
        $token = 'test-verification-token';
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Manually set settings via update to avoid JSON path query issues in SQLite
        $user->update([
            'settings' => ['email_verification_token' => $token],
        ]);

        $result = $this->authService->verifyEmail($token);

        $this->assertTrue($result);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    /** @test */
    public function it_returns_false_for_invalid_verification_token()
    {
        $result = $this->authService->verifyEmail('invalid-token');

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_send_password_reset_email()
    {
        $user = User::factory()->create();

        $result = $this->authService->sendPasswordReset($user->email);

        $this->assertTrue($result);
        $freshUser = $user->fresh();
        $this->assertNotNull(data_get($freshUser->settings, 'password_reset_token'));
        $this->assertNotNull(data_get($freshUser->settings, 'password_reset_expires'));
    }

    /** @test */
    public function it_returns_false_for_non_existent_email()
    {
        $result = $this->authService->sendPasswordReset('nonexistent@example.com');

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_reset_password_with_valid_token()
    {
        $token = 'test-reset-token';
        $user = User::factory()->create();

        // Manually set settings via update to avoid JSON path query issues in SQLite
        $user->update([
            'settings' => [
                'password_reset_token' => $token,
                'password_reset_expires' => now()->addHours(2)->timestamp,
            ],
        ]);

        $result = $this->authService->resetPassword($token, 'NewPassword123!');

        $this->assertTrue($result);
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
        $this->assertNull(data_get($user->fresh()->settings, 'password_reset_token'));
    }

    /** @test */
    public function it_returns_false_for_expired_reset_token()
    {
        $token = 'expired-token';
        $user = User::factory()->create();

        // Manually set settings via update to avoid JSON path query issues in SQLite
        $user->update([
            'settings' => [
                'password_reset_token' => $token,
                'password_reset_expires' => now()->subHours(3)->timestamp,
            ],
        ]);

        $result = $this->authService->resetPassword($token, 'NewPassword123!');

        $this->assertFalse($result);
    }
}
