<?php

namespace Task\Tests\Feature;

use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use User\Services\UserService;
use Task\Services\TaskService;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected GroupService $groupService;
    protected TaskService $taskService;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = app()->make(UserService::class);
        $this->groupService = app()->make(GroupService::class);
        $this->taskService = app()->make(TaskService::class);
    }

    public function testIndexWithoutAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->get('/groups/' . $group->id . '/tasks')->assertStatus(401);
    }

    public function testIndexWithAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->taskService->store($group, $user, 'task name', 'task description', 2);
        $this->actingAs($user)->get('/groups/' . $group->id . '/tasks')
            ->assertJsonFragment([
                'name' => 'task name',
                'description' => 'task description',
                'slots' => 2
            ])
            ->assertStatus(200);
    }

    public function testGetWithAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $task = $this->taskService->store($group, $user, 'task name', 'task description', 2);
        $this->actingAs($user)->get('/groups/' . $group->id . '/tasks/' . $task->id)
            ->assertJsonFragment([
                'name' => 'task name',
                'description' => 'task description',
                'slots' => 2
            ])
            ->assertStatus(200);
    }

    public function testStore()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->actingAs($user)->post(
            '/groups/' . $group->id . '/tasks',
            [
                'name' => 'task name',
                'description' => 'task description',
                'slots' => 2,
            ])
            ->assertJsonFragment([
                'name' => 'task name',
                'description' => 'task description',
                'slots' => 2,
            ])
            ->assertStatus(201);
    }

    public function testUpdate()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $task = $this->taskService->store($group, $user, 'task name', 'task description', 2);
        $this->actingAs($user)->put(
            '/groups/' . $group->id . '/tasks/' . $task->id,
            [
                'name' => 'new name',
                'description' => 'new description',
                'slots' => 4,
            ])
            ->assertJsonFragment([
                'name' => 'new name',
                'description' => 'new description',
                'slots' => 4,
            ])
            ->assertStatus(200);
    }

    public function testDestroy()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $task = $this->taskService->store($group, $user, 'task name', 'task description', 2);
        $this->actingAs($user)->delete('/groups/' . $group->id . '/tasks/' . $task->id)
            ->assertStatus(200);

        $this->assertSoftDeleted('tasks', [
            'name' => 'task name',
            'description' => 'task description',
            'slots' => 2,
        ]);
    }
}
