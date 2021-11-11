<?php

namespace Group\Tests\Feature;

use Group\Models\GroupUser;
use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use User\Services\UserService;

class GroupControllerTest extends TestCase
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

    public function testIndexForWithoutAuthentication()
    {
        $this->get('/groups')->assertStatus(401);
    }

    public function testIndexForEmptyGroups()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $this->actingAs($user)->get('/groups')->assertExactJson([]);
    }

    public function testIndexForSingleGroup()
    {
        $this->get('/groups')->assertStatus(401);

        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $this->groupService->store($user, 'group name', 'group description');

        $this->actingAs($user)->get('/groups')
            ->assertJsonCount(1)
            ->assertStatus(200)
            ->assertJsonFragment([
                'user_id' => $user->id,
                'name' => 'group name',
                'description' => 'group description'
            ]);
    }

    public function testIndexForMultipleGroups()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');

        $this->groupService->store($user, 'group name', 'group description');
        $this->groupService->store($user, 'new group name', 'new group description');

        $this->actingAs($user)->get('/groups')
            ->assertJsonCount(2)
            ->assertStatus(200)
            ->assertJsonFragment([
                'user_id' => $user->id,
                'name' => 'new group name',
                'description' => 'new group description'
            ]);
    }

    public function testGetForUnknownGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $this->actingAs($user)->get('/groups/1')->assertStatus(404);
    }

    public function testGetWithoutAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->get('/groups/' . $group->id)->assertStatus(401);
    }

    public function testGetForAvailableGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->actingAs($user)->get('/groups/' . $group->id)
            ->assertStatus(200)
            ->assertJsonFragment([
                'user_id' => $user->id,
                'name' => 'group name',
                'description' => 'group description'
            ]);
    }

    public function testStoreWithoutAuthentication()
    {
        $this->post('/groups', ['name' => 'test', 'description' => 'test'])->assertStatus(401);
    }

    public function testStoreWithoutData()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $this->actingAs($user)->post('/groups', [], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonFragment([
                'error' => [
                    'data' => [
                        'name' => ['The name field is required.']
                    ]
                ]
            ]);
    }

    public function testStoreWithValidData()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $this->actingAs($user)
            ->post('/groups', ['name' => 'test', 'description' => 'test'], ['Accept' => 'application/json'])
            ->assertStatus(201);
        $this->assertDatabaseHas('groups', [
            'name' => 'test',
            'description' => 'test'
        ]);
    }

    public function testUpdateWithoutAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->put('/groups/' . $group->id, ['name' => 'test', 'description' => 'test'])->assertStatus(401);
    }

    public function testUpdateForUnknownGroup()
    {
        $this->put('/groups/1', ['name' => 'test', 'description' => 'test'])->assertStatus(404);
    }

    public function testUpdateWithValidData()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->actingAs($user)->put('/groups/' . $group->id, ['name' => 'test', 'description' => 'test'])
            ->assertStatus(200);
    }

    public function testUpdateOfAForeignUser()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $foreignUser = $this->userService->store('Jane Doe', 'jane.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->actingAs($foreignUser)
            ->put('/groups/' . $group->id, ['name' => 'test', 'description' => 'test'], ['Accept' => 'application/json'])
            ->assertExactJson([
                'error' => [
                    'message' => 'Du hast die Gruppe nicht erstellt.'
                ]
            ])
            ->assertStatus(403);
    }

    public function testUserInvites()
    {
        $creator = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');

        $user = $this->userService->store('Jane Doe', 'jane.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->groupService->invite($group, $creator, $user);

        $this->actingAs($user)
            ->get('/users/me/invites')
            ->assertJsonCount(1)
            ->assertStatus(200);
    }

    public function testGroupInvites()
    {
        $creator = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');

        $user = $this->userService->store('Jane Doe', 'jane.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->groupService->invite($group, $creator, $user);

        $this->actingAs($user)
            ->get('/groups/' . $group->id . '/invites')
            ->assertJsonCount(1)
            ->assertStatus(200);
    }

    public function testPartner()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $partner = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $this->groupService->setPartner($group, $user, $partner);

        $this->actingAs($user)
            ->get('/groups/' . $group->id . '/partner')
            ->assertStatus(200);
    }

    public function testStart()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->actingAs($user)
            ->post('/groups/' . $group->id . '/start')
            ->assertStatus(200);
    }

    public function testEnd()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->actingAs($user)
            ->post('/groups/' . $group->id . '/start')
            ->assertStatus(200);
    }

    public function testReset()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->actingAs($user)
            ->post('/groups/' . $group->id . '/start')
            ->assertStatus(200);
    }

    public function testJoin()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $group = $this->groupService->generateCode($group);
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $this->actingAs($newUser)
            ->post('/users/join', ['code' => $group->join_code])
            ->assertStatus(200);

        $this->assertDatabaseHas('group_users', [
            'user_id' => $newUser->id,
            'group_id' => $group->id
        ]);

        $this->actingAs($newUser)
            ->post('/users/join', ['code' => $group->join_code])
            ->assertStatus(200);

        $count = DB::table('group_users')->where('user_id', $newUser->id)->where('group_id', $group->id)->count();

        $this->assertEquals(1, $count);
    }

    public function testLeave()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $this->groupService->addUserToGroup($group, $newUser);

        $this->actingAs($newUser)
            ->post('/groups/' . $group->id . '/leave')
            ->assertStatus(200);
    }

    public function testRemoveUser()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $this->groupService->addUserToGroup($group, $newUser);

        $this->actingAs($user)
            ->delete('/groups/' . $group->id . '/users/' . $newUser->id)
            ->assertStatus(200);
    }

    public function testAccept()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $this->groupService->invite($group, $user, $newUser);

        $this->actingAs($user)
            ->post('/groups/' . $group->id . '/accept')
            ->assertStatus(200);
    }

    public function testDeny()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $this->groupService->invite($group, $user, $newUser);

        $this->actingAs($user)
            ->post('/groups/' . $group->id . '/deny')
            ->assertStatus(200);
    }

    public function testGetGroupUser()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');

        $this->actingAs($user)
            ->get('/groups/' . $group->id . '/users/' . $user->id)
            ->assertJsonFragment([
                'name' => 'John Doe'
            ])
            ->assertStatus(200);
    }

    public function testSetAdmin()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $this->groupService->addUserToGroup($group, $newUser);

        $this->actingAs($user)
            ->put('/groups/' . $group->id . '/users/' . $newUser->id, ['admin' => true])
            ->assertStatus(200);
    }

    public function testInviteUser()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $this->actingAs($user)
            ->post('/groups/' . $group->id . '/invites', ['email' => $newUser->email])
            ->assertStatus(200);
    }

    public function testRemoveInvite()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $newUser = $this->userService->store('Jane Doe', 'jane.doe@example.com', 'secret');

        $this->groupService->invite($group, $user, $newUser);

        /** @var GroupUser $groupUser */
        $groupUser = GroupUser::query()
            ->where('group_id', $group->id)
            ->where('user_id', $newUser->id)
            ->first();

        $this->actingAs($user)
            ->delete('/groups/' . $group->id . '/invites/' . $groupUser->id)
            ->assertStatus(200);
    }
}
