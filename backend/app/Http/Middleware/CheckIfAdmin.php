<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Responses\ErrorResponse;

class CheckIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return auth()->user()->hasRole('hub_admin') || auth()->user()->hasRole('super_admin') ?
               $next($request):
               new ErrorResponse('Permission Denied', 401);
    }
}
