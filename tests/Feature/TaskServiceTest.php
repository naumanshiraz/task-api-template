<?php

namespace Tests\Unit\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use App\Services\CacheService;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TaskService $service;
    protected TaskRepository $repository;
    protected CacheService $cacheService;

    /**
     * Set up test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TaskRepository::class);
        $this->cacheService = Mockery::mock(CacheService::class);
        $this->service = new TaskService($this->repository, $this->cacheService);
    }

    /**
     * Test create task invalidates cache.
     */
    public function test_create_task_invalidates_cache(): void
    {
        $data = [
            'project_id' => 1,
            'title' => 'Test Task',
            'description' => 'Test Description',
        ];

        $task = Task::factory()->create($data);

        $this->repository->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($task);

        $this->cacheService->shouldReceive('invalidate')
            ->once();

        $result = $this->service->createTask($data);

        $this->assertEquals($task->id, $result->id);
    }

    /**
     * Test get task by id throws exception when not found.
     */
    public function test_get_task_by_id_throws_on_not_found(): void
    {
        $this->repository->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(\Exception::class);
        $this->service->getTaskById(999);
    }

    /**
     * Tear down test case.
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}