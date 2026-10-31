<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    private const API_PREFIX = '/api/admin/users';
    private ?User $adminUser = null;
    private ?string $adminToken = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and create admin
        DB::table('roles')->insert([
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full access', 'is_system' => true],
            ['name' => 'Student', 'slug' => 'student', 'description' => 'Student', 'is_system' => true],
            ['name' => 'Instructor', 'slug' => 'instructor', 'description' => 'Instructor', 'is_system' => true],
        ]);

        $this->adminUser = User::factory()->create();
        DB::table('user_roles')->insert([
            'user_id' => $this->adminUser->id,
            'role_id' => 1, // Super Admin
        ]);
        $this->adminToken = $this->adminUser->createToken('admin-token')->plainTextToken;
    }

    /** @test */
    public function admin_can_list_all_users()
    {
        User::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('meta.total', 4); // 3 created + 1 admin
    }

    /** @test */
    public function admin_can_search_users()
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . '?search=John');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'John Doe');
    }

    /** @test */
    public function admin_can_filter_users_by_active_status()
    {
        User::factory()->create(['is_active' => true]);
        User::factory()->create(['is_active' => false]);
        User::factory()->create(['is_active' => true]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . '?is_active=true');

        $response->assertOk();
        $data = $response->json('data');
        foreach ($data as $user) {
            $this->assertTrue(filter_var($user['is_active'], FILTER_VALIDATE_BOOLEAN));
        }
    }

    /** @test */
    public function admin_can_show_user_details()
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . "/{$user->id}");

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user.id', $user->id);
    }

    /** @test */
    public function admin_gets_404_for_nonexistent_user()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . '/999999');

        $response->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'User not found']);
    }

    /** @test */
    public function admin_can_update_user()
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson(self::API_PREFIX . "/{$user->id}", [
                'name' => 'Updated Name',
                'is_active' => true,
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals('Updated Name', $user->fresh()->name);
    }

    /** @test */
    public function admin_can_ban_user()
    {
        // Create a non-admin user (student role)
        $user = User::factory()->create(['is_banned' => false, 'is_active' => true]);
        DB::table('user_roles')->insert([
            'user_id' => $user->id,
            'role_id' => 2, // Student
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson(self::API_PREFIX . "/{$user->id}/ban", [
                'reason' => 'Violation of terms',
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $freshUser = $user->fresh();
        $this->assertTrue($freshUser->is_banned);
        $this->assertFalse($freshUser->is_active);
    }

    /** @test */
    public function admin_cannot_ban_another_admin()
    {
        // Admin tries to ban admin 1 (the setUp admin is admin ID 1)
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson(self::API_PREFIX . '/1/ban', [
                'reason' => 'Cannot ban admin',
            ]);

        $response->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Cannot ban an admin user']);
    }

    /** @test */
    public function admin_can_unban_user()
    {
        $user = User::factory()->create(['is_banned' => true, 'is_active' => false]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson(self::API_PREFIX . "/{$user->id}/unban");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $freshUser = $user->fresh();
        $this->assertFalse($freshUser->is_banned);
        $this->assertTrue($freshUser->is_active);
    }

    /** @test */
    public function admin_can_assign_role()
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson(self::API_PREFIX . "/{$user->id}/assign-role", [
                'role' => 'instructor',
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertTrue($user->fresh()->hasRole('instructor'));
    }

    /** @test */
    public function admin_can_list_all_roles()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . '/roles');

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data'); // Super Admin, Student, Instructor
    }

    /** @test */
    public function non_admin_gets_forbidden()
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(self::API_PREFIX);

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_gets_unauthorized()
    {
        $response = $this->getJson(self::API_PREFIX);

        $response->assertStatus(401);
    }
}
