<?php

namespace Group\Services;

use Carbon\Carbon;
use Group\Models\Group;
use Group\Models\GroupPartner;
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
        $groupIds = GroupUser::query()->where('user_id', $user->id)->pluck('group_id');

        return Group::query()
            ->whereIn('id', $groupIds->toArray())
            ->orWhere('user_id', $user->id)
            ->get();
    }

    /**
     * @param User $user
     * @param string $name
     * @param string|null $description
     * @return Group
     */
    public function store(User $user, string $name, ?string $description): Group
    {
        $group = new Group();
        $group->user_id = $user->id;
        $group->name = $name;
        $group->description = $description;
        $group->status = 'start';
        $group->save();

        $this->addUserToGroup($group, $user, true);

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

        if (array_key_exists('status', $input)) {
            $group->status = $input['status'];
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

    /**
     * @param User $user
     * @return Collection
     */
    public function userInvites(User $user): Collection
    {
        return GroupUser::query()
            ->where('user_id', $user->id)
            ->whereNull('joined_at')
            ->get();
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function invites(Group $group): Collection
    {
        return $group->users()->wherePivotNull('joined_at')->get();
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function users(Group $group): Collection
    {
        return $group->users()->wherePivotNotNull('joined_at')->get();
    }

    /**
     * @param Group $group
     * @param User $creator
     * @param User $user
     */
    public function invite(Group $group, User $creator, User $user)
    {
        $groupUser = new GroupUser();
        $groupUser->group_id = $group->id;
        $groupUser->creator_id = $creator->id;
        $groupUser->user_id = $user->id;
        $groupUser->joined_at = null;
        $groupUser->save();
    }

    /**
     * @param Group $group
     * @param User $user
     * @param User $partner
     */
    public function setPartner(Group $group, User $user, User $partner)
    {
        $groupPartner = new GroupPartner();
        $groupPartner->group_id = $group->id;
        $groupPartner->user_id = $user->id;
        $groupPartner->partner_id = $partner->id;
        $groupPartner->save();
    }

    /**
     * @param Group $group
     * @param User $user
     * @return User|null
     */
    public function partner(Group $group, User $user): ?User
    {
        $groupPartner = GroupPartner::query()
            ->where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->first();

        if ($groupPartner === null) {
            return null;
        }

        return $groupPartner->partner;
    }

    /**
     * @param Group $group
     * @return Group
     */
    public function start(Group $group): Group
    {
        $group->status = 'running';
        $group->started_at = Carbon::now();
        $group->save();

        $userIds = $group->users()->wherePivotNotNull('joined_at')->pluck('users.id')->toArray();
        $partnerIds = $this->findPartners($userIds);

        foreach ($partnerIds as $key => $partnerId) {
            /** @var User $user */
            $user = User::query()->find($userIds[$key]);
            /** @var User $partner */
            $partner = User::query()->find($partnerId);
            $this->setPartner($group, $user, $partner);
        }

        return $group;
    }

    /**
     * @param Group $group
     * @return Group
     */
    public function end(Group $group): Group
    {
        $group->status = 'end';
        $group->ended_at = Carbon::now();
        $group->save();

        return $group;
    }

    /**
     * @param Group $group
     * @return Group
     */
    public function reset(Group $group): Group
    {
        $group->status = 'start';
        $group->started_at = null;
        $group->ended_at = null;
        $group->save();

        return $group;
    }

    /**
     * @param Group $group
     * @return Group
     */
    public function generateCode(Group $group): Group
    {
        $group->join_code = sha1(uniqid().time());
        $group->save();

        return $group;
    }

    /**
     * @param Group $group
     * @return Group
     */
    public function resetCode(Group $group): Group
    {
        $group->join_code = null;
        $group->save();

        return $group;
    }

    public function join(User $user, string $code): bool
    {
        /** @var Group $group */
        $group = Group::query()->where('join_code', $code)->first();

        if ($group === null) {
            return false;
        }

        $this->addUserToGroup($group, $user);

        return true;
    }

    /**
     * @param Group $group
     * @param User $user
     * @param bool $admin
     */
    public function addUserToGroup(Group $group, User $user, bool $admin = false): void
    {
        $group->users()->attach($user->id, ['joined_at' => Carbon::now(), 'is_admin' => $admin]);
    }

    /**
     * @param Group $group
     * @param User $user
     */
    public function removeUserFromGroup(Group $group, User $user)
    {
        $group->users()->detach($user->id);
    }

    /**
     * @param Group $group
     * @param User $user
     */
    public function accept(Group $group, User $user)
    {
        GroupUser::query()
            ->where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->whereNull('joined_at')
            ->update(['joined_at' => Carbon::now()]);
    }

    /**
     * @param Group $group
     * @param User $user
     */
    public function deny(Group $group, User $user)
    {
        GroupUser::query()
            ->where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->whereNull('joined_at')
            ->delete();
    }

    public function getUser(Group $group, User $user): ?User
    {
        return $group->users()
            ->wherePivot('group_id', $group->id)
            ->wherePivot('user_id', $user->id)
            ->first();
    }

    public function setAdmin(Group $group, User $user, bool $admin)
    {
        GroupUser::query()
            ->where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->update(['is_admin' => $admin]);
    }

    /**
     * @param Group $group
     * @param User $user
     * @param string $email
     * @return bool
     */
    public function inviteUserWithEmail(Group $group, User $user, string $email): bool
    {
        /** @var User $userToInvite */
        $userToInvite = User::query()->where('email', $email)->first();

        if ($userToInvite === null) {
            return false;
        }

        $this->invite($group, $user, $userToInvite);

        return true;
    }

    /**
     * @param GroupUser $groupUser
     */
    public function removeInvite(GroupUser $groupUser)
    {
        $groupUser->delete();
    }

    /**
     * @param array $objects
     * @return array
     */
    public function findPartners(array $objects): array
    {
        $pickedObjects = [];
        $partnerIds = [];

        if (sizeof($objects) === 0) {
            return [];
        }

        if (sizeof($objects) === 1) {
            return [];
        }

        foreach ($objects as $object) {
            $availableObjects = array_diff($objects, $pickedObjects);
            $availableObjects = array_diff($availableObjects, [$object]);

            if (sizeof($availableObjects) === 0) {
                return $this->findPartners($objects);
            }

            $pickedObject = $availableObjects[array_rand($availableObjects)];
            $partnerIds[] = $pickedObject;
            $pickedObjects[] = $pickedObject;
        }

        return $partnerIds;
    }
}
