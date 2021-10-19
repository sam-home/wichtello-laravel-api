<?php

namespace Message\Tests\Unit;

use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use User\Services\UserService;
use Message\Services\MessageService;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected GroupService $groupService;
    protected MessageService $messageService;

    /**
     * @throws BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->userService = app()->make(UserService::class);
        $this->groupService = app()->make(GroupService::class);
        $this->messageService = app()->make(MessageService::class);
    }

    public function testIndexWithoutWithoutWishesInGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $messages = $this->messageService->index($group);

        $this->assertEquals([], $messages->toArray());
    }

    public function testIndexWithoutWishWishesInGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->messageService->store($group, $user, 'message');
        $messages = $this->messageService->index($group);

        $this->assertCount(1, $messages);
    }

    public function testStore()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->messageService->store($group, $user, 'message');

        $this->assertDatabaseHas('wishes', [
            'user_id' => $user->id,
            'group_id' => $group->id,
            'content' => 'message'
        ]);
    }
}
