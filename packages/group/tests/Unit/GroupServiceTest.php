<?php

namespace Group\Tests\Unit;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Group\Services\GroupService;
use Illuminate\Support\Facades\DB;
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

    public function testPartner()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $partner = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $this->groupService->setPartner($group, $user, $partner);

        $partner = $this->groupService->partner($group, $user);
        $this->assertNotNull($partner);
        $this->assertEquals('Jane Doe', $partner->name);
    }

    public function testStart()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->assertTrue(
            DB::table('groups')
                ->where('id', $group->id)
                ->whereNull('started_at')
                ->exists());

        $group = $this->groupService->start($group);

        $this->assertTrue(
            DB::table('groups')
                ->where('id', $group->id)
                ->whereNotNull('started_at')
                ->exists());
    }

    public function testEnd()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->assertTrue(
            DB::table('groups')
                ->where('id', $group->id)
                ->whereNull('ended_at')
                ->exists());

        $group = $this->groupService->end($group);

        $this->assertTrue(
            DB::table('groups')
                ->where('id', $group->id)
                ->whereNotNull('ended_at')
                ->exists());
    }

    public function testReset()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->assertTrue(
            DB::table('groups')
                ->where('id', $group->id)
                ->whereNull('started_at')
                ->whereNull('ended_at')
                ->exists());

        $group = $this->groupService->start($group);
        $group = $this->groupService->end($group);

        $this->assertTrue(
            DB::table('groups')
                ->where('id', $group->id)
                ->whereNotNull('started_at')
                ->whereNotNull('ended_at')
                ->exists());
    }

    public function testCode()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->assertTrue(
            DB::table('groups')
                ->where('id', $group->id)
                ->whereNull('join_code')
                ->exists());

        $group = $this->groupService->generateCode($group);

        $this->assertTrue(
            DB::table('groups')
                ->where('id', $group->id)
                ->whereNotNull('join_code')
                ->exists());

        $group = $this->groupService->resetCode($group);

        $this->assertTrue(
            DB::table('groups')
                ->where('id', $group->id)
                ->whereNull('join_code')
                ->exists());
    }

    public function testJoin()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $group = $this->groupService->generateCode($group);

        $this->groupService->join($newUser, $group->join_code);

        $this->assertTrue(
            DB::table('group_users')
                ->where('group_id', $group->id)
                ->where('user_id', $newUser->id)
                ->whereNotNull('joined_at')
                ->exists());
    }
}
