<?php

namespace Message\Services;

use User\Models\User;
use Group\Models\Group;
use Illuminate\Support\Collection;
use Message\Models\Message;

class MessageService
{
    /**
     * @param Group $group
     * @return Collection
     */
    public function index(Group $group): Collection
    {
        return Message::query()->where('group_id', $group->id)->get();
    }

    /**
     * @param Group $group
     * @param User $user
     * @param string $content
     * @return Message
     */
    public function store(Group $group, User $user, string $content): Message
    {
        $message = new Message();
        $message->group_id = $group->id;
        $message->user_id = $user->id;
        $message->content = $content;
        $message->save();

        return $message;
    }
}
