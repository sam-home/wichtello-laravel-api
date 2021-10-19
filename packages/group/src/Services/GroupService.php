<?php

namespace Group\Services;

use Group\Models\Group;
use Group\Models\GroupUser;
use Illuminate\Support\Collection;
use User\Models\User;

class GroupService {
    /**
     * @param User $user
     * @return Collection
     */
    public function index(User $user): Collection
    {
        return Group::query()->where('user_id', $user->id)->get();
    }

    /**
     * @param User $user
     * @param string $name
     * @param string $description
     * @param string $state
     * @return Group
     */
    public function store(User $user, string $name, string $description, string $state = 'invite'): Group
    {
        $group = new Group();
        $group->user_id = $user->id;
        $group->name = $name;
        $group->description = $description;
        $group->state = $state;
        $group->save();

        return $group;
    }

    /**
     * @param Group $group
     * @param array $input
     * @return Group
     */
    public function update(Group $group, array $input): Group
    {
        if (array_key_exists('name', $input)) {
            $group->name = $input['name'];
        }

        if (array_key_exists('description', $input)) {
            $group->description = $input['description'];
        }

        if (array_key_exists('state', $input)) {
            $group->state = $input['state'];
        }

        $group->save();

        return $group;
    }

    /**
     * @param Group $group
     * @return bool
     */
    public function destroy(Group $group): bool
    {
        return $group->delete() === true;
    }

    public function invites(User $user): Collection
    {
        return GroupUser::query()
            ->where('user_id', $user->id)
            ->whereNull('joined_at')
            ->get();
    }

    public function invite(Group $group, User $creator, User $user)
    {
        $groupUser = new GroupUser();
        $groupUser->group_id = $group->id;
        $groupUser->creator_id = $creator->id;
        $groupUser->user_id = $user->id;
        $groupUser->save();
    }
}