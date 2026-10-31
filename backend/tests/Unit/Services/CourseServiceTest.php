<?php

namespace Tests\Unit\Services;

use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\User;
use App\Services\CourseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseServiceTest extends TestCase
{
    use RefreshDatabase;

    private CourseService $courseService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->courseService = app(CourseService::class);
    }

    /** @test */
    public function it_can_create_a_course()
    {
        $instructor = User::factory()->create();
        $data = [
            'category_id' => null,
            'title' => 'Web Application Security',
            'description' => 'Learn OWASP Top 10',
            'difficulty_level' => 'intermediate',
            'price' => 49.99,
            'is_free' => false,
        ];

        $course = $this->courseService->create($data, $instructor);

        $this->assertInstanceOf(Course::class, $course);
        $this->assertEquals('Web Application Security', $course->title);
        $this->assertEquals($instructor->id, $course->instructor_id);
        $this->assertEquals('intermediate', $course->difficulty_level);
        $this->assertDatabaseHas('courses', ['title' => 'Web Application Security']);
    }

    /** @test */
    public function it_can_update_a_course()
    {
        $course = Course::factory()->create(['title' => 'Old Title']);

        $updated = $this->courseService->update($course, ['title' => 'New Title']);

        $this->assertEquals('New Title', $updated->title);
        $this->assertEquals('New Title', $course->fresh()->title);
    }

    /** @test */
    public function it_can_delete_a_course()
    {
        $course = Course::factory()->create();

        $result = $this->courseService->delete($course);

        $this->assertTrue($result);
        $this->assertSoftDeleted($course);
    }

    /** @test */
    public function it_can_publish_a_course()
    {
        $course = Course::factory()->unpublished()->create();

        $published = $this->courseService->publish($course);

        $this->assertTrue($published->is_published);
        $this->assertNotNull($published->published_at);
    }

    /** @test */
    public function it_can_unpublish_a_course()
    {
        $course = Course::factory()->create(['is_published' => true]);

        $unpublished = $this->courseService->unpublish($course);

        $this->assertFalse($unpublished->is_published);
    }

    /** @test */
    public function it_can_enroll_a_user_in_a_course()
    {
        $course = Course::factory()->free()->create();
        $user = User::factory()->create();

        $enrollment = $this->courseService->enroll($user, $course);

        $this->assertTrue($course->isEnrolled($user));
        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
    }

    /** @test */
    public function it_throws_exception_on_duplicate_enrollment()
    {
        $course = Course::factory()->free()->create();
        $user = User::factory()->create();

        $this->courseService->enroll($user, $course);

        $this->expectException(\Exception::class);
        $this->courseService->enroll($user, $course);
    }

    /** @test */
    public function it_can_add_module_to_course()
    {
        $course = Course::factory()->create();

        $module = $this->courseService->addModule($course, [
            'title' => 'Introduction Module',
            'description' => 'Getting started',
        ]);

        $this->assertInstanceOf(Module::class, $module);
        $this->assertEquals($course->id, $module->course_id);
        $this->assertEquals(1, $module->sort_order);
    }

    /** @test */
    public function it_can_add_lesson_to_module()
    {
        $module = Module::factory()->create();

        $lesson = $this->courseService->addLesson($module, [
            'title' => 'First Lesson',
            'content_type' => 'text',
            'content' => 'Lesson content here',
        ]);

        $this->assertInstanceOf(Lesson::class, $lesson);
        $this->assertEquals($module->id, $lesson->module_id);
        $this->assertEquals(10, $lesson->xp_reward);
    }

    /** @test */
    public function it_tracks_course_progress()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $this->courseService->enroll($user, $course);

        $progress = $this->courseService->getProgress($user, $course);

        $this->assertIsArray($progress);
        $this->assertTrue($progress['enrolled']);
        $this->assertArrayHasKey('progress', $progress);
        $this->assertArrayHasKey('total_lessons', $progress);
    }

    /** @test */
    public function it_returns_not_enrolled_for_unenrolled_user()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $progress = $this->courseService->getProgress($user, $course);

        $this->assertArrayHasKey('enrolled', $progress);
        $this->assertFalse($progress['enrolled']);
    }
}
