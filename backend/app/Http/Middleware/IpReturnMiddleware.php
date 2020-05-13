<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class IpReturnMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if($response->headers->get('content-type') == 'application/json') {
            $content = json_decode($response->content(), true);
            $content = json_encode(array_merge($content, [
                'metadata' => [
                    'secured' => ($request->get('ip_address') === env("SECURED_IP_ADDRESS")),
                    'ip_address' => $request->get('ip_address')
                ]
            ]));

            return $response->setContent($content);
        } else {
            return $response;
        }


    }
}
