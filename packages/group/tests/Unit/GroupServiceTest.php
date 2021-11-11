<?php

namespace Group\Tests\Unit;

use Group\Models\GroupUser;
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

    public function testIndexOtherUsers()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $otherUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $group = $this->groupService->store($user, 'group name', 'group description');

        $groups = $this->groupService->index($user);
        $this->assertCount(1, $groups);

        $groups = $this->groupService->index($otherUser);
        $this->assertCount(0, $groups);

        $this->groupService->addUserToGroup($group, $otherUser);
        $groups = $this->groupService->index($otherUser);
        $this->assertCount(1, $groups);
    }

    public function testStore()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');

        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->assertDatabaseHas('groups', [
            'user_id' => $user->id,
            'name' => 'group name',
            'description' => 'group description',
            'status' => 'start'
        ]);

        $this->assertTrue(
            DB::table('group_users')
                ->where('group_id', $group->id)
                ->where('user_id', $user->id)
                ->whereNotNull('joined_at')
                ->exists());
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

        $group = $this->groupService->getGroupWithCode($group->join_code);

        if ($group !== null) {
            $this->groupService->addUserToGroup($group, $newUser);
        }

        $this->assertTrue(
            DB::table('group_users')
                ->where('group_id', $group->id)
                ->where('user_id', $newUser->id)
                ->whereNotNull('joined_at')
                ->exists());
    }

    public function testGetGroupWithCode()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $group = $this->groupService->generateCode($group);

        $group = $this->groupService->getGroupWithCode($group->join_code);

        $this->assertNotNull($group);
    }

    public function testAddUserToGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->groupService->addUserToGroup($group, $newUser);

        $this->assertDatabaseHas('group_users', [
            'group_id' => $group->id,
            'user_id' => $newUser->id,
        ]);
    }

    public function testRemoveUserFromGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');
        $otherUser = $this->userService->store('Mary Doe', 'mary.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->groupService->addUserToGroup($group, $newUser);
        $this->groupService->addUserToGroup($group, $otherUser);

        $this->groupService->removeUserFromGroup($group, $newUser);

        $this->assertDatabaseMissing('group_users', [
            'group_id' => $group->id,
            'user_id' => $newUser->id,
        ]);

        $this->assertDatabaseHas('group_users', [
            'group_id' => $group->id,
            'user_id' => $otherUser->id,
        ]);
    }

    public function testAccept()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->groupService->invite($group, $user, $newUser);

        $this->assertTrue(
            DB::table('group_users')
                ->where('group_id', $group->id)
                ->where('user_id', $newUser->id)
                ->whereNull('joined_at')
                ->exists());

        $this->groupService->accept($group, $newUser);

        $this->assertTrue(
            DB::table('group_users')
                ->where('group_id', $group->id)
                ->where('user_id', $newUser->id)
                ->whereNotNull('joined_at')
                ->exists());
    }

    public function testDeny()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->groupService->invite($group, $user, $newUser);

        $this->assertDatabaseHas('group_users', [
            'group_id' => $group->id,
            'user_id' => $newUser->id,
            'joined_at' => null
        ]);

        $this->groupService->deny($group, $newUser);

        $this->assertSoftDeleted('group_users', [
            'group_id' => $group->id,
            'user_id' => $newUser->id
        ]);
    }

    public function testGetUser()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->assertNull($this->groupService->getUser($group, $newUser));
        $this->assertNotNull($this->groupService->getUser($group, $user));
    }

    public function testSetUser()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->groupService->addUserToGroup($group, $newUser);

        $this->assertDatabaseHas('group_users', [
            'group_id' => $group->id,
            'user_id' => $newUser->id,
            'is_admin' => 0
        ]);

        $this->groupService->setAdmin($group, $newUser, true);

        $this->assertDatabaseHas('group_users', [
            'group_id' => $group->id,
            'user_id' => $newUser->id,
            'is_admin' => 1
        ]);
    }

    public function testInviteWithEmail()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->groupService->inviteUserWithEmail($group, $user, $newUser->email);

        $this->assertDatabaseHas('group_users', [
            'group_id' => $group->id,
            'creator_id' => $user->id,
            'user_id' => $newUser->id,
            'joined_at' => null
        ]);
    }

    public function testRemoveInvite()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->groupService->invite($group, $user, $newUser);

        $this->assertDatabaseHas('group_users', [
            'group_id' => $group->id,
            'creator_id' => $user->id,
            'user_id' => $newUser->id,
            'joined_at' => null
        ]);

        /** @var GroupUser $groupUser */
        $groupUser = GroupUser::query()
            ->where('group_id', $group->id)
            ->where('user_id', $newUser->id)
            ->first();

        $this->assertNotNull($groupUser);

        $this->groupService->removeInvite($groupUser);

        $this->assertSoftDeleted('group_users', [
            'group_id' => $group->id,
            'creator_id' => $user->id,
            'user_id' => $newUser->id,
            'joined_at' => null
        ]);
    }

    public function testFindPartners()
    {
        $ids = [10, 4, 2, 45, 23];

        $partnerIds = $this->groupService->findPartners($ids);

        $this->assertEquals(sizeof($ids), sizeof($partnerIds));

        foreach ($ids as $key => $id) {
            $this->assertNotEquals($partnerIds[$key], $id);
            $this->assertTrue(in_array($id, $partnerIds));
        }
    }
}
