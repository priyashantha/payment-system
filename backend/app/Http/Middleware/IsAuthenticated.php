<?php

namespace App\Http\Middleware;

use JWTAuth;

use Closure;

class IsAuthenticated
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
        $payload = JWTAuth::getPayload(JWTAuth::getToken())->toArray();
        if($payload['is_authenticated'] == 1){
            return $next($request);
        }else{
            return response()->json([
                'status' => 'error',
                'msg' => 'Unauthorized access'
            ],401);
        }
    }
}
