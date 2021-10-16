<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GroupUserController extends Controller
{
    public function index(Group $group)
    {
        return $group->users;
    }

    public function store(Group $group, User $user)
    {
        $group->users()->attach(
            $user->id,
            [
                'joined_at' => Carbon::now()
            ]
        );

        return $this->success();
    }

    public function update(Group $group, User $user, Request $request)
    {

    }

    public function delete(Group $group, User $user)
    {
        GroupUser::query()
            ->where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->delete();

        return $this->success();
    }
}
