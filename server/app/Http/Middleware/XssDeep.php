<?php

namespace App\Http\Middleware;

use Closure;

class XssDeep
{
    /**
     * [作用] 全局过滤 $request 防止xss
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $data = xss_safe_filter($request->all());
        foreach ($data as $key => $value) {
            $request->request->set($key, $value);
        }
        return $next( $request );
    }
}
