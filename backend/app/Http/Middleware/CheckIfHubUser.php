<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Responses\ErrorResponse;

class CheckIfHubUser
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
        return auth()->user()->is_hub_user ? 
               $next($request):
               new ErrorResponse('Permission Denied',401);
    }
}
