<?php

namespace Group;

use App\Models\Group;
use Group\Policies\GroupPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Route;

class ServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Group::class => GroupPolicy::class
    ];

    public function boot()
    {
        $this->registerPolicies();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Route::middleware('api')
            ->group(realpath(__DIR__ . '/../routes/routes.php'));
    }
}
