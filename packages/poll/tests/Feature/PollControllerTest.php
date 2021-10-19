<?php

namespace Poll\Tests\Feature;

use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use User\Services\UserService;
use Poll\Services\PollService;

class PollControllerTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected GroupService $groupService;
    protected PollService $pollService;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = app()->make(UserService::class);
        $this->groupService = app()->make(GroupService::class);
        $this->pollService = app()->make(PollService::class);
    }

    public function testIndexWithoutAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->get('/group/' . $group->id . '/polls')->assertStatus(401);
    }

    public function testIndexWithAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $poll = $this->pollService->store($group, $user, 'poll name', 'poll description', ['option 1']);
        $this->actingAs($user)->get('/group/' . $group->id . '/polls')
            ->assertJsonFragment([
                'name' => 'poll name',
                'description' => 'poll description',
            ])
            ->assertStatus(200);
    }

    public function testGetWithAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $poll = $this->pollService->store($group, $user, 'task name', 'task description', ['option 1']);
        $this->actingAs($user)->get('/group/' . $group->id . '/polls/' . $poll->id)
            ->assertJsonFragment([
                'name' => 'task name',
                'description' => 'task description'
            ])
            ->assertJsonStructure(['name', 'description', 'options'])
            ->assertStatus(200);
    }

    public function testStore()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->actingAs($user)->post(
            '/group/' . $group->id . '/polls',
            [
                'name' => 'poll name',
                'description' => 'poll description',
                'options' => ['option 1']
            ])
            ->assertJsonFragment([
                'name' => 'poll name',
                'description' => 'poll description',
            ])
            ->assertJsonStructure(['name', 'description', 'options'])
            ->assertStatus(201);
    }

    public function testUpdate()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $poll = $this->pollService->store($group, $user, 'poll name', 'poll description', ['option 1']);
        $this->actingAs($user)->put(
            '/group/' . $group->id . '/polls/' . $poll->id,
            [
                'name' => 'new name',
                'description' => 'new description',
                'options' => ['new option 1']
            ])
            ->assertJsonFragment([
                'name' => 'new name',
                'description' => 'new description',
            ])
            ->assertJsonStructure(['name', 'description', 'options'])
            ->assertStatus(200);
    }

    public function testDestroy()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $poll = $this->pollService->store($group, $user, 'poll name', 'poll description', ['option 1']);
        $this->actingAs($user)->delete('/group/' . $group->id . '/polls/' . $poll->id)
            ->assertStatus(200);

        $this->assertSoftDeleted('polls', [
            'name' => 'poll name',
            'description' => 'poll description',
        ]);

        $this->assertSoftDeleted('poll_options', [
            'poll_id' => $poll->id,
            'content' => 'option 1'
        ]);
    }
}
