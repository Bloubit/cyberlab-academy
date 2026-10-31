<?php

namespace Tests\Feature\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminCourseControllerTest extends TestCase
{
    use RefreshDatabase;

    private const API_PREFIX = '/api/admin/courses';
    private ?User $adminUser = null;
    private ?string $adminToken = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user with role
        $this->adminUser = User::factory()->create();
        DB::table('roles')->insert([
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full access', 'is_system' => true],
        ]);
        DB::table('user_roles')->insert([
            'user_id' => $this->adminUser->id,
            'role_id' => 1,
        ]);
        $this->adminToken = $this->adminUser->createToken('admin-token')->plainTextToken;
    }

    /** @test */
    public function admin_can_list_all_courses()
    {
        Course::factory()->count(3)->create(['is_published' => true]);
        Course::factory()->create(['is_published' => false]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(4, 'data');
    }

    /** @test */
    public function admin_can_filter_courses_by_publish_status()
    {
        Course::factory()->count(3)->create(['is_published' => true]);
        Course::factory()->create(['is_published' => false]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . '?is_published=true');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function admin_can_search_courses()
    {
        Course::factory()->create(['title' => 'Web Security Fundamentals', 'is_published' => true]);
        Course::factory()->create(['title' => 'Network Pentesting', 'is_published' => true]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . '?search=Web');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Web Security Fundamentals');
    }

    /** @test */
    public function admin_can_show_course_details()
    {
        $course = Course::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . "/{$course->id}");

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.id', $course->id);
    }

    /** @test */
    public function admin_gets_404_for_nonexistent_course()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . '/999999');

        $response->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Course not found']);
    }

    /** @test */
    public function admin_can_create_course()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson(self::API_PREFIX, [
                'title' => 'New Security Course',
                'description' => 'A comprehensive security course',
                'difficulty_level' => 'intermediate',
                'is_free' => true,
                'is_published' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.title', 'New Security Course');

        $this->assertDatabaseHas('courses', ['title' => 'New Security Course']);
    }

    /** @test */
    public function admin_cannot_create_course_without_title()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson(self::API_PREFIX, [
                'description' => 'Missing title',
            ]);

        $response->assertStatus(500);
    }

    /** @test */
    public function admin_can_update_course()
    {
        $course = Course::factory()->create(['title' => 'Old Title']);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson(self::API_PREFIX . "/{$course->id}", [
                'title' => 'Updated Title',
                'is_featured' => true,
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals('Updated Title', $course->fresh()->title);
        $this->assertTrue($course->fresh()->is_featured);
    }

    /** @test */
    public function admin_can_delete_course()
    {
        $course = Course::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson(self::API_PREFIX . "/{$course->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    }

    /** @test */
    public function admin_can_toggle_course_publish_status()
    {
        $course = Course::factory()->create(['is_published' => false]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson(self::API_PREFIX . "/{$course->id}/toggle-publish");

        $response->assertOk()
            ->assertJson(['success' => true]);
        $this->assertTrue($course->fresh()->is_published);

        // Toggle back
        $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson(self::API_PREFIX . "/{$course->id}/toggle-publish");
        $this->assertFalse($course->fresh()->is_published);
    }

    /** @test */
    public function admin_can_list_course_categories()
    {
        CourseCategory::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson(self::API_PREFIX . '/categories/list');

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function admin_can_create_category()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson(self::API_PREFIX . '/categories', [
                'name' => 'Cloud Security',
                'description' => 'Cloud security courses',
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('course_categories', ['name' => 'Cloud Security']);
    }

    /** @test */
    public function admin_can_update_category()
    {
        $category = CourseCategory::factory()->create(['name' => 'Old Category']);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson(self::API_PREFIX . "/categories/{$category->id}", [
                'name' => 'Updated Category',
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals('Updated Category', $category->fresh()->name);
    }

    /** @test */
    public function admin_can_delete_empty_category()
    {
        $category = CourseCategory::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson(self::API_PREFIX . "/categories/{$category->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertModelMissing($category);
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
