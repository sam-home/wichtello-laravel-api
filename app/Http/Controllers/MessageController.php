<?php

namespace App\Http\Controllers;

use App\Models\ClientEvent;
use App\Models\Group;
use App\Models\Message;
use App\Notifications\ChatMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function index(Group $group, Request $request)
    {
        $query = $group->messages()->with('user')->orderBy('id', 'DESC');

        $offset = $request->has('offset') ? $request->get('offset') : 0;

        $messages = $query->offset($offset)->limit(15)->get();

        return $messages->sortBy('id')->values()->all();
    }

    public function store(Group $group, Request $request)
    {
        $request->validate([
            'content' => 'required'
        ]);

        $message = new Message();
        $message->group_id = $group->id;
        $message->user_id = auth()->user()->id;
        $message->content = $request->get('content');
        $message->save();

        ClientEvent::sendGroup($group, 'new:message', $message);

        $group->notify(new ChatMessageNotification($message));

        return ['success' => true];
    }
}
