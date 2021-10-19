<?php

namespace Event\Services;

use User\Models\User;
use Group\Models\Group;
use Illuminate\Support\Collection;
use Event\Models\Event;

class EventService
{
    /**
     * @param Group $group
     * @return Collection
     */
    public function index(Group $group): Collection
    {
        return Event::query()->where('group_id', $group->id)->get();
    }

    /**
     * @param Group $group
     * @param User $user
     * @param string $name
     * @param string $content
     * @return Event
     */
    public function store(Group $group, User $user, string $name, string $content): Event
    {
        $wish = new Event();
        $wish->group_id = $group->id;
        $wish->user_id = $user->id;
        $wish->name = $name;
        $wish->content = $content;
        $wish->save();

        return $wish;
    }
}
