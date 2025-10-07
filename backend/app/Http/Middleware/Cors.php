<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Responses\ErrorResponse;

class Cors
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
        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', env('HEADER_ALLOWED_ORIGINS', '*'));
        $response->headers->set('Access-Control-Allow-Credentials', true);
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With,Content-Type,X-Token-Auth,Authorization');
        $response->headers->set('Accept', 'application/json');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');;
        return $response;
    }
}
