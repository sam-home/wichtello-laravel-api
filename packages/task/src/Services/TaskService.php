<?php

namespace Task\Services;

use User\Models\User;
use Group\Models\Group;
use Illuminate\Support\Collection;
use Task\Models\Task;

class TaskService
{
    /**
     * @param Group $group
     * @return Collection
     */
    public function index(Group $group): Collection
    {
        return Task::query()->where('group_id', $group->id)->get();
    }

    /**
     * @param Group $group
     * @param User $user
     * @param string $name
     * @param string $description
     * @param int $slots
     * @return Task
     */
    public function store(Group $group, User $user, string $name, string $description, int $slots): Task
    {
        $task = new Task();
        $task->group_id = $group->id;
        $task->user_id = $user->id;
        $task->name = $name;
        $task->description = $description;
        $task->slots = $slots;
        $task->save();

        return $task;
    }

    /**
     * @param Task $task
     * @param array $data
     * @return Task
     */
    public function update(Task $task, array $data): Task
    {
        if (array_key_exists('name', $data)) {
            $task->name = $data['name'];
        }

        if (array_key_exists('description', $data)) {
            $task->description = $data['description'];
        }

        if (array_key_exists('slots', $data)) {
            $task->slots = $data['slots'];
        }

        $task->save();

        return $task;
    }

    /**
     * @param Task $task
     * @return bool
     */
    public function destroy(Task $task): bool
    {
        return $task->delete() === true;
    }

    /**
     * @param Task $task
     * @param User $user
     * @param string|null $comment
     */
    public function join(Task $task, User $user, ?string $comment = null)
    {
        $task->users()->attach($user->id, ['comment' => $comment ?? '']);
    }

    /**
     * @param Task $task
     * @param User $user
     */
    public function leave(Task $task, User $user)
    {
        $task->users()->detach($user->id);
    }
}
