<?php

namespace Group\Tests\Feature;

use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
                'description' => 'group description',
                'state' => 'invite'
            ]);
    }

    public function testIndexForMultipleGroups()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');

        $this->groupService->store($user, 'group name', 'group description');
        $this->groupService->store($user, 'new group name', 'new group description', 'started');

        $this->actingAs($user)->get('/groups')
            ->assertJsonCount(2)
            ->assertStatus(200)
            ->assertJsonFragment([
                'user_id' => $user->id,
                'name' => 'new group name',
                'description' => 'new group description',
                'state' => 'started'
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
                'description' => 'group description',
                'state' => 'invite'
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
                'errors' => [
                    'name' => ['The name field is required.'],
                    'description' => ['The description field is required.']
                ],
                'message' => 'The given data was invalid.'
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
                'error' => 'Du hast die Gruppe nicht erstellt.'
            ])
            ->assertStatus(403);
    }

    public function testInvites()
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
}
