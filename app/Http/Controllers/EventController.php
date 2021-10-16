<?php

namespace App\Http\Controllers;

use App\Models\ClientEvent;
use App\Models\Message;
use App\Models\Poll;
use App\Models\Task;
use App\Models\User;
use App\Models\Wish;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $limit = 10;

        $offset = 0;

        if ($request->has('page')) {
            $offset = intval($request->get('page')) * $limit;
        }

        $groups = auth()->user()->groups()->wherePivot('joined_at', '<>', null)->pluck('id');

        return ClientEvent::where(function ($q) use ($groups) {
            $q->whereIn('group_id', $groups);
            $q->orWhere('group_id', null);
        })->where(function ($q) {
            $q->where('user_id', null);
            $q->orWhere('user_id', auth()->user()->id);
        })->orderBy('id', 'DESC')->offset($offset)->limit($limit)->get()->each(function ($event) {
            if (preg_match('/:message$/', $event->name)) {
                $event->content = Message::with(['user', 'group'])->whereId($event->content)->first();
            } else if (preg_match('/:poll/', $event->name)) {
                $event->content = Poll::with(['user', 'group'])->whereId($event->content)->first();
            } else if (preg_match('/:wish/', $event->name)) {
                $event->content = Wish::with(['user', 'group'])->whereId($event->content)->first();
            } else if (preg_match('/:task/', $event->name)) {
                $event->content = Task::with(['user', 'group'])->whereId($event->content)->first();
            }
        });
    }

    public function polling($eventId)
    {
        $event = ClientEvent::where('id', '>', $eventId)->first();
        return ['event' => $event];
    }

    public function longPolling($eventId)
    {
        session_start();
        session_write_close();

        $event = null;

        while ($event === null) {
            sleep(1);
            $event = ClientEvent::where('id', '>', $eventId)->first();
        }

        return $event;
    }

    public function serverSentEvents($eventId, Request $request)
    {

        if (!$request->has('token')) {
            abort(400);
        }

        $token = $request->get('token');
        $user = User::whereToken($token)->first();

        if ($user === null) {
            abort(400);
        }

        if ($request->server->has('HTTP_LAST_EVENT_ID')) {
            $eventId = $request->server->get('HTTP_LAST_EVENT_ID');
        }

        $response = new StreamedResponse(function () use ($eventId) {
            while (true) {
                $event = ClientEvent::where('id', '>', $eventId)->first();

                if ($event !== null) {
                    echo 'id: ' . $event->id . PHP_EOL;
                    echo 'data: ' . json_encode($event) . PHP_EOL;
                    echo PHP_EOL;

                    $eventId = $event->id;

                    ob_flush();
                    flush();
                }
                ob_flush();
                flush();
                usleep(200000);
            }
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cach-Control', 'no-cache');
        return $response;
    }

    public function getLast()
    {
        $event = ClientEvent::orderBy('id', 'DESC')->first();

        if ($event === null) {
            $event = new ClientEvent();
            $event->id = 0;
            return $event;
        }

        return $event;
    }
}
