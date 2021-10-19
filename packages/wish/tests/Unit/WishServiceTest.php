<?php

namespace Wish\Tests\Unit;

use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use User\Services\UserService;
use Wish\Services\WishService;
use Tests\TestCase;

class WishServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected GroupService $groupService;
    protected WishService $wishService;

    /**
     * @throws BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->userService = app()->make(UserService::class);
        $this->groupService = app()->make(GroupService::class);
        $this->wishService = app()->make(WishService::class);
    }

    public function testIndexWithoutWithoutWishesInGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $wishes = $this->wishService->index($group);

        $this->assertEquals([], $wishes->toArray());
    }

    public function testIndexWithoutWishWishesInGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->wishService->store($group, $user, 'pony');
        $wishes = $this->wishService->index($group);

        $this->assertCount(1, $wishes);
    }

    public function testStore()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->wishService->store($group, $user, 'pony');

        $this->assertDatabaseHas('wishes', [
            'user_id' => $user->id,
            'group_id' => $group->id,
            'content' => 'pony'
        ]);
    }

    public function testUpdate()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $wish = $this->wishService->store($group, $user, 'pony');
        $this->wishService->update($wish, 'basketball');

        $this->assertDatabaseHas('wishes', [
            'content' => 'basketball'
        ]);
    }

    public function testDestroy()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $wish = $this->wishService->store($group, $user, 'pony');

        $this->assertNotSoftDeleted('wishes', [
            'content' => 'pony'
        ]);

        $this->wishService->destroy($wish);

        $this->assertSoftDeleted('wishes', [
            'content' => 'pony'
        ]);
    }
}
