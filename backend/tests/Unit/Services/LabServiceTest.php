<?php

namespace Tests\Unit\Services;

use App\Models\Lab;
use App\Models\User;
use App\Services\LabService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabServiceTest extends TestCase
{
    use RefreshDatabase;

    private LabService $labService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->labService = app(LabService::class);
    }

    /** @test */
    public function it_can_create_a_lab()
    {
        $creator = User::factory()->create();
        $data = [
            'title' => 'SQL Injection Lab',
            'difficulty_level' => 'medium',
            'type' => 'challenge',
            'description' => 'Practice SQL injection',
            'xp_reward' => 200,
            'duration_minutes' => 45,
        ];

        $lab = $this->labService->create($data, $creator);

        $this->assertInstanceOf(Lab::class, $lab);
        $this->assertEquals('SQL Injection Lab', $lab->title);
        $this->assertEquals('medium', $lab->difficulty_level);
        $this->assertDatabaseHas('labs', ['title' => 'SQL Injection Lab']);
    }

    /** @test */
    public function it_can_update_a_lab()
    {
        $lab = Lab::factory()->create(['title' => 'Old Lab']);

        $updated = $this->labService->update($lab, ['title' => 'Updated Lab']);

        $this->assertEquals('Updated Lab', $updated->title);
        $this->assertEquals('Updated Lab', $lab->fresh()->title);
    }

    /** @test */
    public function it_can_delete_a_lab()
    {
        $lab = Lab::factory()->create();

        $result = $this->labService->delete($lab);

        $this->assertTrue($result);
        $this->assertSoftDeleted($lab);
    }

    /** @test */
    public function it_can_publish_a_lab()
    {
        $lab = Lab::factory()->unpublished()->create();

        $published = $this->labService->publish($lab);

        $this->assertTrue($published->is_published);
    }

    /** @test */
    public function it_can_search_labs_by_difficulty()
    {
        Lab::factory()->create(['difficulty_level' => 'easy', 'is_published' => true]);
        Lab::factory()->create(['difficulty_level' => 'hard', 'is_published' => true]);

        $results = $this->labService->search(['difficulty_level' => 'easy'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('easy', $results->first()->difficulty_level);
    }

    /** @test */
    public function it_can_search_labs_by_type()
    {
        Lab::factory()->create(['type' => 'challenge', 'is_published' => true]);
        Lab::factory()->create(['type' => 'room', 'is_published' => true]);

        $results = $this->labService->search(['type' => 'challenge'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('challenge', $results->first()->type);
    }

    /** @test */
    public function it_can_search_labs_by_keyword()
    {
        Lab::factory()->create(['title' => 'SQL Injection Lab', 'is_published' => true]);
        Lab::factory()->create(['title' => 'XSS Lab', 'is_published' => true]);

        $results = $this->labService->search(['search' => 'SQL'])->get();

        $this->assertCount(1, $results);
        $this->assertStringContainsString('SQL', $results->first()->title);
    }
}
