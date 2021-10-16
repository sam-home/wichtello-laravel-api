<?php

namespace App\Http\Controllers;

use App\Models\ClientEvent;
use App\Models\Group;
use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    public function index(Group $group) {
        $polls = $group->polls()->with('options')->get();

        return $polls;
    }

    public function get(Group $group, Poll $poll)
    {
        $poll->options;

        return $poll;
    }

    public function store(Group $group, Request $request)
    {
        $request->validate([
            'name' => 'required',
            'options' => 'required'
        ]);

        $poll = new Poll();

        $poll->user_id = auth()->user()->id;
        $poll->group_id = $group->id;
        $poll->name = $request->get('name');
        $poll->description = $request->get('description') ?? '';
        $poll->save();

        foreach ($request->get('options') as $option) {
            $pollOption = new PollOption();
            $pollOption->poll_id = $poll->id;
            $pollOption->content = $option;
            $pollOption->save();
        }

        ClientEvent::sendGroup($group, 'new:poll', $poll);

        return ['success' => true];
    }

    public function update(Group $group, Poll $poll, Request $request)
    {
        $poll->name = $request->get('name');
        $poll->description = $request->get('description') ?? '';
        $poll->save();

        PollOption::query()->where('poll_id', $poll->id)->delete();

        foreach ($request->get('options') as $option) {
            $pollOption = new PollOption();
            $pollOption->poll_id = $poll->id;
            $pollOption->content = $option;
            $pollOption->save();
        }
    }

    public function remove(Group $group, Poll $poll)
    {
        $poll->delete();

        return ['success' => true];
    }

    public function vote(Group $group, Poll $poll, PollOption $option)
    {
        $user = auth()->user();

        $poll->options->each(function ($option) use ($user) {
            /** @var PollOption $option */
            $option->users()->detach($user->id);
        });

        $option->users()->attach($user->id);

        return ['success' => true];
    }

    public function reset(Group $group, Poll $poll)
    {
        $user = auth()->user();

        $poll->options->each(function ($option) use ($user) {
            /** @var PollOption $option */
            $option->users()->detach($user->id);
        });

        return ['success' => true];
    }
}
