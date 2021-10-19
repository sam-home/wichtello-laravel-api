<?php

namespace Message\Tests\Feature;

use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use User\Services\UserService;
use Message\Services\MessageService;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected GroupService $groupService;
    protected MessageService $messageService;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = app()->make(UserService::class);
        $this->groupService = app()->make(GroupService::class);
        $this->messageService = app()->make(MessageService::class);
    }

    public function testIndexWithoutAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->get('/group/' . $group->id . '/messages')->assertStatus(401);
    }

    public function testIndexWithAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->messageService->store($group, $user, 'message');
        $this->actingAs($user)->get('/group/' . $group->id . '/messages')
            ->assertJsonFragment([
                'content' => 'message'
            ])
            ->assertStatus(200);
    }

    public function testStore()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->actingAs($user)->post('/group/' . $group->id . '/messages', ['content' => 'message'])
            ->assertJsonFragment([
                'content' => 'message'
            ])
            ->assertStatus(201);
    }
}
