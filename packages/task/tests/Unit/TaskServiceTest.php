<?php

namespace Task\Tests\Unit;

use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use User\Services\UserService;
use Task\Services\TaskService;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected GroupService $groupService;
    protected TaskService $taskService;

    /**
     * @throws BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->userService = app()->make(UserService::class);
        $this->groupService = app()->make(GroupService::class);
        $this->taskService = app()->make(TaskService::class);
    }

    public function testIndexWithoutWithoutWishesInGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $wishes = $this->taskService->index($group);

        $this->assertEquals([], $wishes->toArray());
    }

    public function testIndexWithoutWishWishesInGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->taskService->store($group, $user, 'task name', 'task description', 2);
        $tasks = $this->taskService->index($group);

        $this->assertCount(1, $tasks);
    }

    public function testStore()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->taskService->store($group, $user, 'task name', 'task description', 2);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'group_id' => $group->id,
            'name' => 'task name',
            'description' => 'task description',
            'slots' => 2
        ]);
    }

    public function testUpdate()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $task = $this->taskService->store($group, $user, 'task name', 'task description', 2);
        $this->taskService->update($task, [
            'name' => 'new name',
            'description' => 'new description',
            'slots' => 4
        ]);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'group_id' => $group->id,
            'name' => 'new name',
            'description' => 'new description',
            'slots' => 4
        ]);
    }

    public function testDestroy()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $task = $this->taskService->store($group, $user, 'task name', 'task description', 2);

        $this->assertNotSoftDeleted('tasks', [
            'name' => 'task name',
            'description' => 'task description',
        ]);

        $this->taskService->destroy($task);

        $this->assertSoftDeleted('tasks', [
            'name' => 'task name',
            'description' => 'task description',
        ]);
    }

    public function testJoin()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $task = $this->taskService->store($group, $user, 'task name', 'task description', 2);

        $this->taskService->join($task, $user, 'the comment');

        $this->assertDatabaseHas('task_users', [
            'user_id' => $user->id,
            'comment' => 'the comment'
        ]);
    }

    public function testLeave()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $task = $this->taskService->store($group, $user, 'task name', 'task description', 2);

        $this->taskService->join($task, $user, 'the comment');
        $this->taskService->leave($task, $user);

        $this->assertDatabaseMissing('task_users', [
            'user_id' => $user->id,
            'comment' => 'the comment'
        ]);
    }
}
