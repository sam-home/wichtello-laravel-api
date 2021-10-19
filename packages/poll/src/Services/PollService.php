<?php

namespace Poll\Services;

use Poll\Models\PollOption;
use User\Models\User;
use Group\Models\Group;
use Illuminate\Support\Collection;
use Poll\Models\Poll;

class PollService
{
    /**
     * @param Group $group
     * @return Collection
     */
    public function index(Group $group): Collection
    {
        return Poll::query()->with('options')->where('group_id', $group->id)->get();
    }

    /**
     * @param Group $group
     * @param User $user
     * @param string $name
     * @param string $description
     * @param array $options
     * @return Poll
     */
    public function store(Group $group, User $user, string $name, string $description, array $options): Poll
    {
        $poll = new Poll();
        $poll->group_id = $group->id;
        $poll->user_id = $user->id;
        $poll->name = $name;
        $poll->description = $description;
        $poll->save();

        $this->savePollOptions($poll, $options);

        $poll->load('options');

        return $poll;
    }

    /**
     * @param Poll $poll
     * @param array $data
     * @return Poll
     */
    public function update(Poll $poll, array $data): Poll
    {
        if (array_key_exists('name', $data)) {
            $poll->name = $data['name'];
        }

        if (array_key_exists('description', $data)) {
            $poll->description = $data['description'];
        }

        if (array_key_exists('options', $data)) {
            PollOption::query()->where('poll_id', $poll->id)->delete();
            $this->savePollOptions($poll, $data['options']);
        }

        $poll->save();

        $poll->load('options');

        return $poll;
    }

    /**
     * @param Poll $poll
     * @return bool
     */
    public function destroy(Poll $poll): bool
    {
        PollOption::query()->where('poll_id', $poll->id)->delete();
        return $poll->delete() === true;
    }

    /**
     * @param Poll $poll
     * @param array $options
     */
    protected function savePollOptions(Poll $poll, array $options): void
    {
        foreach ($options as $option) {
            $pollOption = new PollOption();
            $pollOption->poll_id = $poll->id;
            $pollOption->content = $option;
            $pollOption->save();
        }
    }
}
