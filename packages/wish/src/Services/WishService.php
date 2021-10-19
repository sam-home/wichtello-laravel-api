<?php

namespace Wish\Services;

use User\Models\User;
use Group\Models\Group;
use Illuminate\Support\Collection;
use Wish\Models\Wish;

class WishService
{
    /**
     * @param Group $group
     * @return Collection
     */
    public function index(Group $group): Collection
    {
        return Wish::query()->where('group_id', $group->id)->get();
    }

    /**
     * @param Group $group
     * @param User $user
     * @param string $content
     * @return Wish
     */
    public function store(Group $group, User $user, string $content): Wish
    {
        $wish = new Wish();
        $wish->group_id = $group->id;
        $wish->user_id = $user->id;
        $wish->content = $content;
        $wish->save();

        return $wish;
    }

    /**
     * @param Wish $wish
     * @param string $content
     * @return Wish
     */
    public function update(Wish $wish, string $content): Wish
    {
        $wish->content = $content;
        $wish->save();

        return $wish;
    }

    /**
     * @param Wish $wish
     * @return bool
     */
    public function destroy(Wish $wish): bool
    {
        return $wish->delete() === true;
    }
}
