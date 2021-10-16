<?php

namespace App\Broadcasting;

use App\Models\Broadcast;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DatabaseBroadcaster extends Broadcaster
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function auth($request)
    {
        if (Str::startsWith($request->channel_name, ['private-', 'presence-']) &&
            !$request->user()) {
            throw new AccessDeniedHttpException;
        }

        $channelName = Str::startsWith($request->channel_name, 'private-')
            ? Str::replaceFirst('private-', '', $request->channel_name)
            : Str::replaceFirst('presence-', '', $request->channel_name);

        return parent::verifyUserCanAccessChannel(
            $request,
            $channelName
        );
    }

    public function canAccessChannel($user, $channel)
    {
        $channel = Str::startsWith($channel, 'private-')
            ? Str::replaceFirst('private-', '', $channel)
            : Str::replaceFirst('presence-', '', $channel);

        foreach ($this->channels as $pattern => $callback) {
            if (! Str::is(preg_replace('/\{(.*?)\}/', '*', $pattern), $channel)) {
                continue;
            }

            $parameters = $this->extractAuthParameters($pattern, $channel, $callback);

            if ($result = $callback($user, ...$parameters)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Request $request
     * @param mixed $result
     * @return false|mixed|string
     */
    public function validAuthenticationResponse($request, $result)
    {
        if (is_bool($result)) {
            return json_encode($result);
        }

        return json_encode(['channel_data' => [
            'user_id' => $request->user()->getAuthIdentifier(),
            'user_info' => $result,
        ]]);
    }

    /**
     * @param array $channels
     * @param string $event
     * @param array $payload
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $broadcast = new Broadcast();
        $broadcast->event = $event;
        $broadcast->channels = $this->formatChannels($channels);
        $broadcast->payload = $payload;
        $broadcast->save();
    }
}
