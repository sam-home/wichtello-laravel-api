<?php

namespace Event\Controllers;

use Group\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use User\Models\User;
use Event\Models\Event;
use Event\Services\EventService;

class EventController
{
    public function __construct(public EventService $eventService) {
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function index(Group $group): Collection
    {
        return $this->eventService->index($group);
    }
}
