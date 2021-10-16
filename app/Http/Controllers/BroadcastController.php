<?php

namespace App\Http\Controllers;

use App\Services\BroadcastService;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    protected $broadcastService;

    public function __construct(BroadcastService $broadcastService)
    {
        $this->broadcastService = $broadcastService;
    }

    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $lastBroadcastId = $request->get('id', null);

        $broadcasts = $this->broadcastService->waitForBroadcast($user, $lastBroadcastId);

        return $broadcasts;
    }
}
