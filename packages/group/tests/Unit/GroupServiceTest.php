<?php

namespace Group\Tests\Unit;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Group\Services\GroupService;
use Tests\TestCase;
use User\Services\UserService;

class GroupServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected GroupService $groupService;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = app()->make(UserService::class);
        $this->groupService = app()->make(GroupService::class);
    }

    public function testIndex()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');

        $groups = $this->groupService->index($user);
        $this->assertEquals([], $groups->toArray());
    }

    public function testStore()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');

        $this->groupService->store($user, 'group name', 'group description');

        $this->assertDatabaseHas('groups', [
            'user_id' => $user->id,
            'name' => 'group name',
            'description' => 'group description'
        ]);
    }

    public function testUpdate()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');

        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->groupService->update($group, ['name' => 'new group name', 'description' => 'new group description']);

        $this->assertDatabaseHas('groups', [
            'user_id' => $user->id,
            'name' => 'new group name',
            'description' => 'new group description'
        ]);
    }

    public function testDelete()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');

        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->assertNotSoftDeleted('groups', [
            'user_id' => $user->id,
            'name' => 'group name',
            'description' => 'group description'
        ]);

        $success = $this->groupService->destroy($group);

        $this->assertTrue($success);

        $this->assertSoftDeleted('groups', [
            'user_id' => $user->id,
            'name' => 'group name',
            'description' => 'group description'
        ]);
    }

    public function testUserInvites()
    {
        $creator = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');

        $group = $this->groupService->store($creator, 'group name', 'group description');

        $user = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $invitations = $this->groupService->userInvites($user);

        $this->assertEmpty($invitations);

        $this->groupService->invite($group, $creator, $user);

        $invitations = $this->groupService->userInvites($user);

        $this->assertNotEmpty($invitations);
    }

    public function testGroupInvites()
    {
        $creator = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');

        $group = $this->groupService->store($creator, 'group name', 'group description');

        $user = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $invitations = $this->groupService->invites($group);

        $this->assertEmpty($invitations);

        $this->groupService->invite($group, $creator, $user);

        $invitations = $this->groupService->invites($group);

        $this->assertNotEmpty($invitations);
    }

    public function testSetPartner()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $partner = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $this->groupService->setPartner($group, $user, $partner);

        $this->assertDatabaseHas('group_partners', [
            'group_id' => $group->id,
            'user_id' => $user->id,
            'partner_id' => $partner->id,
        ]);
    }

    public function testGetPartner()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $partner = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $this->groupService->setPartner($group, $user, $partner);

        $partner = $this->groupService->partner($group, $user);
        $this->assertNotNull($partner);
        $this->assertEquals('Jane Doe', $partner->name);
    }
}
