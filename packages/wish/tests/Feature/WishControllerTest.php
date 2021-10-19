<?php

namespace Wish\Tests\Feature;

use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use User\Services\UserService;
use Wish\Services\WishService;

class WishControllerTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected GroupService $groupService;
    protected WishService $wishService;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = app()->make(UserService::class);
        $this->groupService = app()->make(GroupService::class);
        $this->wishService = app()->make(WishService::class);
    }

    public function testIndexWithoutAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->get('/groups/' . $group->id . '/wishes')->assertStatus(401);
    }

    public function testIndexWithAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->wishService->store($group, $user, 'pony');
        $this->actingAs($user)->get('/groups/' . $group->id . '/wishes')
            ->assertJsonFragment([
                'content' => 'pony'
            ])
            ->assertStatus(200);
    }

    public function testGetWithAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $wish = $this->wishService->store($group, $user, 'pony');
        $this->actingAs($user)->get('/groups/' . $group->id . '/wishes/' . $wish->id)
            ->assertJsonFragment([
                'content' => 'pony'
            ])
            ->assertStatus(200);
    }

    public function testStore()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->actingAs($user)->post('/groups/' . $group->id . '/wishes', ['content' => 'pony'])
            ->assertJsonFragment([
                'content' => 'pony'
            ])
            ->assertStatus(201);
    }

    public function testUpdate()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $wish = $this->wishService->store($group, $user, 'pony');
        $this->actingAs($user)->put('/groups/' . $group->id . '/wishes/' . $wish->id, ['content' => 'basketball'])
            ->assertJsonFragment([
                'content' => 'basketball'
            ])
            ->assertStatus(200);
    }

    public function testDestroy()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $wish = $this->wishService->store($group, $user, 'pony');
        $this->actingAs($user)->delete('/groups/' . $group->id . '/wishes/' . $wish->id)
            ->assertStatus(200);

        $this->assertSoftDeleted('wishes', [
            'content' => 'pony'
        ]);
    }
}
