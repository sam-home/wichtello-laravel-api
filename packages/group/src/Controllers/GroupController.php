<?php

namespace Group\Controllers;

use Group\Models\Group;
use Group\Services\GroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use User\Models\User;
use User\Services\UserService;

class GroupController
{
    public function __construct(public UserService $userService, public GroupService $groupService) {
    }

    public function index(): Collection
    {
        /** @var User $user */
        $user = auth()->user();
        return $this->groupService->index($user);
    }

    /**
     * @param Group $group
     * @return Group
     */
    public function get(Group $group): Group
    {
        return $group;
    }

    public function store(Request $request): Group
    {
        $input = $request->validate([
            'name' => 'required',
            'description' => 'required'
        ]);

        /** @var User $user */
        $user = auth()->user();
        return $this->groupService->store($user, $input['name'], $input['description']);
    }

    public function update(Group $group, Request $request): Group
    {
        Gate::authorize('update', $group);

        $input = $request->validate([
            'name' => 'sometimes',
            'description' => 'sometimes',
            'state' => 'sometimes'
        ]);

        return $this->groupService->update($group, $input);
    }

    public function invites(): Collection
    {
        $user = $this->userService->getAuthenticatedUser();

        return $this->groupService->invites($user);
    }
}
