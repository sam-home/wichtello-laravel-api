<?php

namespace Task;

use Illuminate\Support\Facades\Route;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Route::middleware('api')
            ->group(realpath(__DIR__ . '/../routes/routes.php'));
    }
}
