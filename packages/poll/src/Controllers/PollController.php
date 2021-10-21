<?php

namespace Poll\Controllers;

use Group\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Poll\Models\PollOption;
use User\Models\User;
use Poll\Models\Poll;
use Poll\Services\PollService;
use User\Services\UserService;

class PollController
{
    public function __construct(public PollService $pollService, public UserService $userService) {
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function index(Group $group): Collection
    {
        return $this->pollService->index($group);
    }

    /**
     * @param Group $group
     * @param Poll $poll
     * @return Poll
     */
    public function get(Group $group, Poll $poll): Poll
    {
        $poll->load(['options']);
        return $poll;
    }

    /**
     * @param Group $group
     * @param Request $request
     * @return Poll
     */
    public function store(Group $group, Request $request): Poll
    {
        $input = $request->validate([
            'name' => 'required',
            'description' => 'required',
            'options' => 'required',
        ]);

        /** @var User $user */
        $user = auth()->user();

        return $this->pollService->store($group, $user, $input['name'], $input['description'], $input['options']);
    }

    /**
     * @param Group $group
     * @param Poll $poll
     * @param Request $request
     * @return Poll
     */
    public function update(Group $group, Poll $poll, Request $request): Poll
    {
        $input = $request->validate([
            'name' => 'sometimes',
            'description' => 'sometimes',
            'options' => 'sometimes',
        ]);

        return $this->pollService->update($poll, $input);
    }

    /**
     * @param Group $group
     * @param Poll $poll
     */
    public function destroy(Group $group, Poll $poll)
    {
        $this->pollService->destroy($poll);
    }

    public function select(Group $group, Poll $poll, PollOption $pollOption)
    {
        $user = $this->userService->getAuthenticatedUser();

        $this->pollService->select($user, $pollOption);
    }

    public function unselect(Group $group, Poll $poll, PollOption $pollOption)
    {
        $user = $this->userService->getAuthenticatedUser();

        $this->pollService->unselect($user, $pollOption);
    }
}
