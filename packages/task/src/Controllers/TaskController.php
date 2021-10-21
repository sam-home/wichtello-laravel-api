<?php

namespace Task\Controllers;

use Group\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use User\Models\User;
use Task\Models\Task;
use Task\Services\TaskService;
use User\Services\UserService;

class TaskController
{
    public function __construct(private TaskService $taskService, private UserService $userService) {
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function index(Group $group): Collection
    {
        return $this->taskService->index($group);
    }

    /**
     * @param Group $group
     * @param Task $task
     * @return Task
     */
    public function get(Group $group, Task $task): Task
    {
        return $task;
    }

    /**
     * @param Group $group
     * @param Request $request
     * @return Task
     */
    public function store(Group $group, Request $request): Task
    {
        $input = $request->validate([
            'name' => 'required',
            'description' => 'required',
            'slots' => 'required',
        ]);

        /** @var User $user */
        $user = auth()->user();

        return $this->taskService->store($group, $user, $input['name'], $input['description'], $input['slots']);
    }

    /**
     * @param Group $group
     * @param Task $task
     * @param Request $request
     * @return Task
     */
    public function update(Group $group, Task $task, Request $request): Task
    {
        $input = $request->validate([
            'name' => 'sometimes',
            'description' => 'sometimes',
            'slots' => 'sometimes',
        ]);

        return $this->taskService->update($task, $input);
    }

    /**
     * @param Group $group
     * @param Task $task
     */
    public function destroy(Group $group, Task $task)
    {
        $this->taskService->destroy($task);
    }

    /**
     * @param Group $group
     * @param Task $task
     */
    public function join(Group $group, Task $task, Request $request)
    {
        $input = $request->validate([
            'comment' => 'sometimes'
        ]);

        $user = $this->userService->getAuthenticatedUser();
        $this->taskService->join($task, $user, $input['comment']);
    }

    /**
     * @param Group $group
     * @param Task $task
     */
    public function leave(Group $group, Task $task)
    {
        $user = $this->userService->getAuthenticatedUser();
        $this->taskService->leave($task, $user);
    }
}
