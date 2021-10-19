<?php

namespace Message\Controllers;

use Group\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use User\Models\User;
use Message\Models\Message;
use Message\Services\MessageService;

class MessageController
{
    public function __construct(public MessageService $messageService) {
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function index(Group $group): Collection
    {
        return $this->messageService->index($group);
    }

    /**
     * @param Group $group
     * @param Request $request
     * @return Message
     */
    public function store(Group $group, Request $request): Message
    {
        $input = $request->validate([
            'content' => 'required'
        ]);

        /** @var User $user */
        $user = auth()->user();

        return $this->messageService->store($group, $user, $input['content']);
    }
}
