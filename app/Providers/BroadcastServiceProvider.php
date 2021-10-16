<?php

namespace App\Providers;

use App\Broadcasting\DatabaseBroadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @param BroadcastManager $broadcastManager
     * @return void
     */
    public function boot(BroadcastManager $broadcastManager)
    {
        // Broadcast::routes();

        $broadcastManager->extend('database', function ($app, array $config) {
            return new DatabaseBroadcaster();
        });

        require base_path('routes/channels.php');
    }
}
