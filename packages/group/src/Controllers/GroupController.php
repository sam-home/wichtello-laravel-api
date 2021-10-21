<?php

namespace Group\Controllers;

use Group\Models\Group;
use Group\Models\GroupUser;
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

    /**
     * @param Request $request
     * @return Group
     */
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

    /**
     * @param Group $group
     * @param Request $request
     * @return Group
     */
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

    /**
     * @param Group $group
     * @return Collection
     */
    public function invites(Group $group): Collection
    {
        return $this->groupService->invites($group);
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function users(Group $group): Collection
    {
        return $this->groupService->users($group);
    }

    /**
     * @return Collection
     */
    public function userInvites(): Collection
    {
        $user = $this->userService->getAuthenticatedUser();

        return $this->groupService->userInvites($user);
    }

    /**
     * @param Group $group
     * @return User|null
     */
    public function partner(Group $group): ?User
    {
        $user = $this->userService->getAuthenticatedUser();

        return $this->groupService->partner($group, $user);
    }

    /**
     * @param Group $group
     * @return Group
     */
    public function start(Group $group): Group
    {
        return $this->groupService->start($group);
    }

    /**
     * @param Group $group
     * @return Group
     */
    public function end(Group $group): Group
    {
        return $this->groupService->end($group);
    }

    /**
     * @param Group $group
     * @return Group
     */
    public function reset(Group $group): Group
    {
        return $this->groupService->reset($group);
    }

    /**
     * @param Group $group
     * @return Group
     */
    public function generateCode(Group $group): Group
    {
        return $this->groupService->generateCode($group);
    }

    /**
     * @param Group $group
     * @return Group
     */
    public function resetCode(Group $group): Group
    {
        return $this->groupService->resetCode($group);
    }

    /**
     * @param Request $request
     */
    public function join(Request $request)
    {
        $input = $request->validate([
            'code' => 'required'
        ]);

        $user = $this->userService->getAuthenticatedUser();

        $this->groupService->join($user, $input['code']);
    }

    /**
     * @param Group $group
     */
    public function leave(Group $group)
    {
        $user = $this->userService->getAuthenticatedUser();

        $this->groupService->removeUserFromGroup($group, $user);
    }

    /**
     * @param Group $group
     * @param User $userToRemove
     */
    public function removeUser(Group $group, User $userToRemove)
    {
        $this->groupService->removeUserFromGroup($group, $userToRemove);
    }

    /**
     * @param Group $group
     */
    public function accept(Group $group)
    {
        $user = $this->userService->getAuthenticatedUser();

        $this->groupService->accept($group, $user);
    }

    /**
     * @param Group $group
     */
    public function deny(Group $group)
    {
        $user = $this->userService->getAuthenticatedUser();

        $this->groupService->deny($group, $user);
    }

    /**
     * @param Group $group
     * @param User $user
     * @return User|null
     */
    public function getUser(Group $group, User $user): ?User
    {
        return $this->groupService->getUser($group, $user);
    }

    /**
     * @param Group $group
     * @param User $user
     * @param Request $request
     */
    public function updateUser(Group $group, User $user, Request $request)
    {
        $input = $request->validate([
            'admin' => 'required|boolean'
        ]);

        $this->groupService->setAdmin($group, $user, boolval($input['admin']));
    }

    /**
     * @param Group $group
     * @param Request $request
     */
    public function inviteUser(Group $group, Request $request)
    {
        $input = $request->validate([
            'email' => 'required|email'
        ]);

        $user = $this->userService->getAuthenticatedUser();

        $this->groupService->inviteUserWithEmail($group, $user, $input['email']);
    }

    /**
     * @param Group $group
     * @param GroupUser $groupUser
     */
    public function removeInvite(Group $group, GroupUser  $groupUser)
    {
        $this->groupService->removeInvite($groupUser);
    }
}
