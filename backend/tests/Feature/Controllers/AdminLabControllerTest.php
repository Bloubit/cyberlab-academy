<?php

namespace Tests\Feature\Controllers;

use App\Models\Lab;
use App\Models\User;
use App\Models\Challenge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminLabControllerTest extends TestCase
{
    use RefreshDatabase;

    private const API_PREFIX = '/api/admin/labs';
    private ?User $adminUser = null;
    private ?string $adminToken = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and create admin
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
    public function admin_can_list_all_labs()
    {
        Lab::factory()->count(3)->create(['is_published' => true]);
        Lab::factory()->create(['is_published' => false]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(4, 'data');
    }

    /** @test */
    public function admin_can_filter_labs_by_publish_status()
    {
        Lab::factory()->count(3)->create(['is_published' => true]);
        Lab::factory()->create(['is_published' => false]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . '?is_published=true');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function admin_can_filter_labs_by_difficulty()
    {
        Lab::factory()->create(['difficulty_level' => 'beginner', 'is_published' => true]);
        Lab::factory()->create(['difficulty_level' => 'advanced', 'is_published' => true]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . '?difficulty_level=beginner');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.difficulty_level', 'beginner');
    }

    /** @test */
    public function admin_can_search_labs()
    {
        Lab::factory()->create(['title' => 'SQL Injection Lab']);
        Lab::factory()->create(['title' => 'XSS Challenge']);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . '?search=SQL');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function admin_can_show_lab_details()
    {
        $lab = Lab::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . "/{$lab->id}");

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.id', $lab->id)
            ->assertJsonPath('data.title', $lab->title);
    }

    /** @test */
    public function admin_gets_404_for_nonexistent_lab()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . '/999999');

        $response->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Lab not found']);
    }

    /** @test */
    public function admin_can_create_lab()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson(self::API_PREFIX, [
                'title' => 'New Pentest Lab',
                'description' => 'Practice penetration testing',
                'difficulty_level' => 'intermediate',
                'type' => 'single_machine',
                'duration_minutes' => 60,
                'xp_reward' => 200,
                'is_published' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.title', 'New Pentest Lab');

        $this->assertDatabaseHas('labs', ['title' => 'New Pentest Lab']);
    }

    /** @test */
    public function admin_cannot_create_lab_without_title()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson(self::API_PREFIX, [
                'difficulty_level' => 'beginner',
            ]);

        $response->assertStatus(500);
    }

    /** @test */
    public function admin_can_update_lab()
    {
        $lab = Lab::factory()->create(['title' => 'Old Lab Title']);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson(self::API_PREFIX . "/{$lab->id}", [
                'title' => 'Updated Lab Title',
                'xp_reward' => 500,
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals('Updated Lab Title', $lab->fresh()->title);
        $this->assertEquals(500, $lab->fresh()->xp_reward);
    }

    /** @test */
    public function admin_can_delete_lab()
    {
        $lab = Lab::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson(self::API_PREFIX . "/{$lab->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSoftDeleted('labs', ['id' => $lab->id]);
    }

    /** @test */
    public function admin_can_toggle_lab_publish_status()
    {
        $lab = Lab::factory()->create(['is_published' => false]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson(self::API_PREFIX . "/{$lab->id}/toggle-publish");

        $response->assertOk()
            ->assertJson(['success' => true]);
        $this->assertTrue($lab->fresh()->is_published);
    }

    /** @test */
    public function admin_can_get_lab_challenges()
    {
        $lab = Lab::factory()->create();
        Challenge::factory()->count(3)->create(['lab_id' => $lab->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . "/{$lab->id}/challenges");

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function admin_can_create_challenge_with_flag()
    {
        $lab = Lab::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson(self::API_PREFIX . "/{$lab->id}/challenges", [
                'title' => 'Find the Flag',
                'description' => 'Find the hidden flag in the system',
                'points' => 100,
                'flag' => 'FLAG{test_flag_2024}',
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.title', 'Find the Flag');

        $this->assertDatabaseHas('challenges', ['title' => 'Find the Flag']);
        $this->assertDatabaseHas('flags', ['flag_type' => 'static']);
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
