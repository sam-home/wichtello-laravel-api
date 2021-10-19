<?php

namespace Event\Tests\Feature;

use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use User\Services\UserService;
use Event\Services\EventService;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected GroupService $groupService;
    protected EventService $eventService;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = app()->make(UserService::class);
        $this->groupService = app()->make(GroupService::class);
        $this->eventService = app()->make(EventService::class);
    }

    public function testIndexWithoutAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->get('/group/' . $group->id . '/events')->assertStatus(401);
    }

    public function testIndexWithAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->eventService->store($group, $user, 'event name', 'event description');
        $this->actingAs($user)->get('/group/' . $group->id . '/events')
            ->assertJsonFragment([
                'name' => 'event name',
                'content' => 'event description'
            ])
            ->assertStatus(200);
    }
}
