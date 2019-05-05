<?php

namespace App\Http\Middleware;

use Closure;

/**
 * 允许跨域请求处理
 *
 * Class EnableCrossRequestMiddleware
 * @package App\Http\Middleware
 */
class EnableCrossRequest
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
        $response = $next($request);
        $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : env('APP_URL');
        if (app()->environment() == 'local' || app()->environment() == 'testing') {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Headers',
                'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN');
            $response->headers->set('Access-Control-Expose-Headers', 'Authorization, authenticated');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
        return $response;
    }
}
