<?php

namespace Event\Tests\Unit;

use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use User\Services\UserService;
use Event\Services\EventService;
use Tests\TestCase;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected GroupService $groupService;
    protected EventService $eventService;

    /**
     * @throws BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->userService = app()->make(UserService::class);
        $this->groupService = app()->make(GroupService::class);
        $this->eventService = app()->make(EventService::class);
    }

    public function testIndexWithoutWithoutWishesInGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $events = $this->eventService->index($group);

        $this->assertEquals([], $events->toArray());
    }

    public function testIndexWithoutWishWishesInGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->eventService->store($group, $user, 'event name', 'event description');
        $events = $this->eventService->index($group);

        $this->assertCount(1, $events);
    }
}
