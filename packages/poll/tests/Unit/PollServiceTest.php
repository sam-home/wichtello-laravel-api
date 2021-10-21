<?php

namespace Poll\Tests\Unit;

use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Poll\Models\PollOption;
use User\Services\UserService;
use Poll\Services\PollService;
use Tests\TestCase;

class PollServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected GroupService $groupService;
    protected PollService $pollService;

    /**
     * @throws BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->userService = app()->make(UserService::class);
        $this->groupService = app()->make(GroupService::class);
        $this->pollService = app()->make(PollService::class);
    }

    public function testIndexWithoutWithoutWishesInGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $wishes = $this->pollService->index($group);

        $this->assertEquals([], $wishes->toArray());
    }

    public function testIndexWithoutWishWishesInGroup()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $this->pollService->store($group, $user, 'poll name', 'poll description', ['option 1']);
        $polls = $this->pollService->index($group);

        $this->assertCount(1, $polls);
    }

    public function testStore()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $poll = $this->pollService->store($group, $user, 'poll name', 'poll description', ['option 1']);

        $this->assertDatabaseHas('polls', [
            'user_id' => $user->id,
            'group_id' => $group->id,
            'name' => 'poll name',
            'description' => 'poll description',
        ]);

        $this->assertDatabaseHas('poll_options', [
            'poll_id' => $poll->id,
            'content' => 'option 1'
        ]);
    }

    public function testUpdate()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $poll = $this->pollService->store($group, $user, 'poll name', 'poll description', ['option 1']);
        $this->pollService->update($poll, [
            'name' => 'new name',
            'description' => 'new description',
            'options' => ['new option 1']
        ]);

        $this->assertDatabaseHas('polls', [
            'user_id' => $user->id,
            'group_id' => $group->id,
            'name' => 'new name',
            'description' => 'new description',
        ]);

        $this->assertSoftDeleted('poll_options', [
            'poll_id' => $poll->id,
            'content' => 'option 1'
        ]);

        $this->assertDatabaseHas('poll_options', [
            'poll_id' => $poll->id,
            'content' => 'new option 1'
        ]);
    }

    public function testDestroy()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $poll = $this->pollService->store($group, $user, 'poll name', 'poll description', ['option 1']);

        $this->assertNotSoftDeleted('polls', [
            'name' => 'poll name',
            'description' => 'poll description',
        ]);

        $this->assertNotSoftDeleted('poll_options', [
            'content' => 'option 1'
        ]);

        $this->pollService->destroy($poll);

        $this->assertSoftDeleted('polls', [
            'name' => 'poll name',
            'description' => 'poll description',
        ]);

        $this->assertSoftDeleted('poll_options', [
            'content' => 'option 1'
        ]);
    }

    public function testSelect()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $poll = $this->pollService->store($group, $user, 'poll name', 'poll description', ['option 1']);

        /** @var PollOption $pollOption */
        $pollOption = PollOption::query()->where('poll_id', $poll->id)->first();

        $this->pollService->select($user, $pollOption);

        $this->assertDatabaseHas('poll_user_options', [
            'poll_option_id' => $pollOption->id,
            'user_id' => $user->id
        ]);
    }

    public function testUnselect()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.com', 'secret');
        $group = $this->groupService->store($user, 'group name', 'group description');
        $poll = $this->pollService->store($group, $user, 'poll name', 'poll description', ['option 1']);

        /** @var PollOption $pollOption */
        $pollOption = PollOption::query()->where('poll_id', $poll->id)->first();

        $this->pollService->select($user, $pollOption);
        $this->pollService->unselect($user, $pollOption);

        $this->assertDatabaseMissing('poll_user_options', [
            'poll_option_id' => $pollOption->id,
            'user_id' => $user->id
        ]);
    }
}
