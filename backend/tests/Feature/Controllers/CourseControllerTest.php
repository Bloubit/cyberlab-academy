<?php

namespace Tests\Feature\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseControllerTest extends TestCase
{
    use RefreshDatabase;

    private const API_PREFIX = '/api/courses';

    /** @test */
    public function it_can_list_published_courses()
    {
        Course::factory()->count(3)->create(['is_published' => true]);
        Course::factory()->create(['is_published' => false]);

        $response = $this->getJson(self::API_PREFIX);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_show_a_single_course()
    {
        $course = Course::factory()->create();

        $response = $this->getJson(self::API_PREFIX . "/{$course->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.id', $course->id)
            ->assertJsonPath('data.title', $course->title);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_course()
    {
        $response = $this->getJson(self::API_PREFIX . '/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_can_enroll_in_a_course()
    {
        $user = User::factory()->create();
        $course = Course::factory()->free()->create();

        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(self::API_PREFIX . "/{$course->id}/enroll");

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully enrolled in course',
            ]);

        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
    }

    /** @test */
    public function it_requires_authentication_to_enroll()
    {
        $course = Course::factory()->create();

        $response = $this->postJson(self::API_PREFIX . "/{$course->id}/enroll");

        $response->assertStatus(401);
    }

    /** @test */
    public function it_prevents_duplicate_enrollment()
    {
        $user = User::factory()->create();
        $course = Course::factory()->free()->create();

        $token = $user->createToken('auth-token')->plainTextToken;

        // First enrollment
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(self::API_PREFIX . "/{$course->id}/enroll");

        // Duplicate enrollment
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(self::API_PREFIX . "/{$course->id}/enroll");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Already enrolled in this course',
            ]);
    }

    /** @test */
    public function it_can_get_enrolled_courses()
    {
        $user = User::factory()->create();
        $course1 = Course::factory()->free()->create();
        $course2 = Course::factory()->free()->create();

        $token = $user->createToken('auth-token')->plainTextToken;

        // Enroll in courses with token-based auth
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(self::API_PREFIX . "/{$course1->id}/enroll");
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(self::API_PREFIX . "/{$course2->id}/enroll");

        // Get enrolled courses
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(self::API_PREFIX . '/enrolled');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_can_get_course_progress()
    {
        $user = User::factory()->create();
        $course = Course::factory()->free()->create();

        $token = $user->createToken('auth-token')->plainTextToken;

        // Enroll first with token-based auth
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(self::API_PREFIX . "/{$course->id}/enroll");

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(self::API_PREFIX . "/{$course->id}/progress");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.enrolled', true);
    }

    /** @test */
    public function it_returns_featured_courses()
    {
        Course::factory()->featured()->create(['is_published' => true]);
        Course::factory()->featured()->create(['is_published' => true]);
        Course::factory()->create(['is_published' => true]);

        $response = $this->getJson(self::API_PREFIX . '/featured');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);
    }
}
