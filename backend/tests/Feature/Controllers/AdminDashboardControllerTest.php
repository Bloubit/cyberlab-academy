<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Lab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private const API_PREFIX = '/api/admin/dashboard';
    private ?User $adminUser = null;
    private ?string $adminToken = null;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('roles')->insert([
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full access', 'is_system' => true],
        ]);

        $this->adminUser = User::factory()->create();
        DB::table('user_roles')->insert([
            'user_id' => $this->adminUser->id,
            'role_id' => 1,
        ]);
        $this->adminToken = $this->adminUser->createToken('admin-token')->plainTextToken;
    }

    /** @test */
    public function admin_can_view_dashboard()
    {
        User::factory()->count(3)->create();
        Course::factory()->count(2)->create(['is_published' => true]);
        Course::factory()->create(['is_published' => false, 'is_featured' => true]);
        Lab::factory()->count(2)->create(['is_published' => true]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'stats' => [
                        'users' => ['total', 'active', 'banned', 'new_today'],
                        'courses' => ['total', 'published', 'featured'],
                        'labs' => ['total', 'published'],
                        'badges' => ['total', 'active'],
                        'content' => ['quizzes', 'submissions', 'correct_submissions', 'success_rate'],
                        'revenue' => ['total', 'currency'],
                    ],
                    'recent_users',
                    'recent_transactions',
                ],
            ]);

        $data = $response->json('data.stats');
        $this->assertEquals(7, $data['users']['total']); // 3 created + 1 admin + 3 course instructors
        $this->assertEquals(3, $data['courses']['total']);
        $this->assertEquals(2, $data['courses']['published']);
        $this->assertEquals(2, $data['labs']['total']);
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
