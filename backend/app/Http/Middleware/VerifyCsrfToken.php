<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

use Symfony\Component\HttpFoundation\Cookie;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];
    /*
 * @param  \Illuminate\Http\Request  $request
 * @param  \Illuminate\Http\Response  $response
 * @return \Illuminate\Http\Response
 */

    protected function addCookieToResponse($request, $response)
    {
        $response->headers->setCookie(
            new Cookie('XSRF-TOKEN',
                $request->session()->token(),
                time() + 60 * 120,
                '/',
                null,
                config('session.secure'),
                true)
        );

        return $response;
    }
}
