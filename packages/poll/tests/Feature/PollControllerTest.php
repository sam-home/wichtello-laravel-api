<?php

namespace Poll\Tests\Feature;

use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Poll\Models\PollOption;
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
        $this->get('/groups/' . $group->id . '/polls')->assertStatus(401);
    }

    public function testIndexWithAuthentication()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $poll = $this->pollService->store($group, $user, 'poll name', 'poll description', ['option 1']);
        $this->actingAs($user)->get('/groups/' . $group->id . '/polls')
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
        $this->actingAs($user)->get('/groups/' . $group->id . '/polls/' . $poll->id)
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
            '/groups/' . $group->id . '/polls',
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
            '/groups/' . $group->id . '/polls/' . $poll->id,
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
        $this->actingAs($user)->delete('/groups/' . $group->id . '/polls/' . $poll->id)
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

    public function testSelect()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $poll = $this->pollService->store($group, $user, 'poll name', 'poll description', ['option 1']);

        /** @var PollOption $pollOption */
        $pollOption = PollOption::query()->where('poll_id', $poll->id)->first();

        $this->actingAs($user)->post('/groups/' . $group->id . '/polls/' . $poll->id . '/select/' . $pollOption->id)
            ->assertStatus(200);
    }

    public function testUnselect()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $poll = $this->pollService->store($group, $user, 'poll name', 'poll description', ['option 1']);

        /** @var PollOption $pollOption */
        $pollOption = PollOption::query()->where('poll_id', $poll->id)->first();

        $this->actingAs($user)->post('/groups/' . $group->id . '/polls/' . $poll->id . '/unselect/' . $pollOption->id)
            ->assertStatus(200);
    }
}
