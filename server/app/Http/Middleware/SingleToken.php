<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Support\Facades\Cache;

class SingleToken
{
    /**
     * 单用户验证
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $ip = $request->getClientIp(); // 用户ip
        $header_user_agent = $request->header()['user-agent'][0]; // 用户浏览器信息

        // 制作 token
        $key = 'singleToken:uId:'.$user->id;
        $singleToken = md5($ip . $header_user_agent . $user->id);
        if( Cache::get($key) != $singleToken ){
            Auth::guard('api')->logout();// 拉黑这个 token
            return response(['message' => '登录异常！请重新登录','status_code'=>401],401);
        }
        return $next($request);
    }
}