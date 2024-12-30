<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS')
                ->header('Access-Control-Max-Age', '1000')
                ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token, Authorization, Sec-WebSocket-Protocol');
        }

        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS')
                ->header('Access-Control-Max-Age', '1000')
                ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token, Authorization, Sec-WebSocket-Protocol');
        }

        return $response;
    }

}
