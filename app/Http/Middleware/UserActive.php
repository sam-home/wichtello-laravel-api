<?php

namespace App\Http\Middleware;

use Closure;

class UserActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->user()->active === false) {
            return abort('401', 'access denied');
        }

        return $next($request);
    }
}
