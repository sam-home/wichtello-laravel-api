<?php

namespace App\Http\Controllers;

use App\Models\ClientEvent;
use App\Models\Group;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Group $group) {
        return $group
            ->tasks()
            ->with('users')
            ->get();
    }

    public function get(Group $group, Task $task)
    {
        if ($group->id !== $task->group_id) {
            return $this->error();
        }

        return $task;
    }

    public function store(Group $group, Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'slots' => 'required|numeric|min:1',
            'description' => 'sometimes'
        ]);

        $user = $this->getAuthenticatedUser();
        $name = $request->get('name');
        $description = $request->get('description') ?? '';
        $slots = $request->get('slots');

        $task = new Task();
        $task->group_id = $group->id;
        $task->user_id = $user->id;
        $task->name = $name;
        $task->description = $description;
        $task->slots = $slots;
        $task->save();

        return ['success' => true];
    }

    public function update(Group $group, Task $task, Request $request)
    {
        if ($group->id !== $task->group_id) {
            return $this->error();
        }

        $this->validate($request, [
            'name' => 'required',
            'slots' => 'required|numeric|min:1',
            'description' => 'sometimes'
        ]);

        $name = $request->get('name');
        $description = $request->get('description') ?? '';
        $slots = $request->get('slots');

        $task->name = $name;
        $task->description = $description;
        $task->slots = $slots;
        $task->save();

        return $this->success();
    }

    public function remove(Group $group, Task $task)
    {
        if ($group->id !== $task->group_id) {
            return $this->error();
        }

        $task->delete();

        return $this->success();
    }

    public function signIn(Group $group, Task $task, Request $request)
    {
        if ($task->user_count >= $task->slots) {
            return ['success' => false];
        }

        $task->users()->attach(auth()->user()->id, ['comment' => $request->get('comment') ?? '']);

        return ['success' => true];
    }

    public function signOut(Group $group, Task $task)
    {
        $task->users()->detach(auth()->user()->id);

        return ['success' => true];
    }
}
