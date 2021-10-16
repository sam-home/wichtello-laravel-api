<?php

namespace App\Services;

use App\Broadcasting\DatabaseBroadcaster;
use App\Models\Broadcast;
use App\Models\User;
use Exception;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\Collection;

class BroadcastService
{
    protected $broadcastManager;

    public function __construct(BroadcastManager $broadcastManager)
    {
        $this->broadcastManager = $broadcastManager;
    }

    /**
     * Wartet auf Broadcasts und gibt diese dann aus
     * @param User $user
     * @param int $startBroadcastId
     * @param int $timeout
     * @return Collection<Broadcast>
     */
    public function waitForBroadcast(User $user, $startBroadcastId = 0, $timeout = 30)
    {
        $lastBroadcastId = $this->getLastBroadcastId($startBroadcastId);

        do {
            $broadcasts = $this->getNewBroadcasts($user, $lastBroadcastId);

            if ($broadcasts->count() > 0) {
                return $broadcasts;
            }

            $lastBroadcastId = $this->getLastBroadcastId();

            sleep(1);
            $timeout--;
        } while ($timeout > 0);

        return collect();
    }

    public function getNewBroadcasts(User $user, $lastBroadcastId)
    {
        $broadcasts = Broadcast::query()->where('id', '>', $lastBroadcastId)->get();

        return $this->filterBroadcastsByUser($broadcasts, $user);
    }

    /**
     * @param Collection $broadcasts
     * @param User $user
     * @return Collection
     */
    public function filterBroadcastsByUser(Collection $broadcasts, User $user) {
        return $broadcasts->filter(function (Broadcast $broadcast) use ($user) {
            foreach ($broadcast->channels as $channel) {
                if ($this->broadcastManager->canAccessChannel($user, $channel)) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    /**
     * @param null $startBroadcastId
     * @return int
     */
    public function getLastBroadcastId($startBroadcastId = null)
    {
        /** @var Broadcast $lastBroadcast */
        $lastBroadcast = Broadcast::query()->latest('id')->first();

        if ($lastBroadcast === null) {
            return 0;
        }

        if ($startBroadcastId === null) {
            return $lastBroadcast->id;
        }

        if ($startBroadcastId <= $lastBroadcast->id) {
            return $startBroadcastId;
        }

        return $lastBroadcast->id;
    }
}
