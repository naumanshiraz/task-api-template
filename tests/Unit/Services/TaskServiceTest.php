<?php

namespace Tests\Unit\Services;

use App\Models\Task;
use App\Services\TaskService;
use App\Services\CacheService;
use App\Repositories\TaskRepository;
use Tests\TestCase;
use Mockery;

class TaskServiceTest extends TestCase
{
    private TaskService $service;
    private TaskRepository $repository;
    private CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TaskRepository::class);
        $this->cacheService = Mockery::mock(CacheService::class);
        $this->service = new TaskService($this->repository, $this->cacheService);
    }

    public function test_create_task_invalidates_cache()
    {
        $data = [
            'project_id' => 1,
            'title' => 'Test Task',
            'description' => 'Test Description',
        ];

        $task = Task::factory()->create($data);

        $this->repository->shouldReceive('create')->once()->with($data)->andReturn($task);
        $this->cacheService->shouldReceive('invalidate')->once();

        $result = $this->service->createTask($data);

        $this->assertEquals($task->id, $result->id);
    }

    public function test_get_task_by_id_throws_on_not_found()
    {
        $this->repository->shouldReceive('findById')->once()->andReturn(null);

        $this->expectException(\Exception::class);
        $this->service->getTaskById(999);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}