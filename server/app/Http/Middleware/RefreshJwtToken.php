<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Illuminate\Support\Facades\Cache;

class RefreshJwtToken extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|mixed
     * @throws JWTException
     */
    public function handle($request, Closure $next)
    {
        $this->checkForToken($request);

        try {
            // 检测用户的登录状态，如果正常则通过
            if ($this->auth->parseToken()->authenticate()) {
                return $next($request);
            }
            throw new UnauthorizedHttpException('jwt-auth', '未登录');
        } catch (TokenExpiredException $exception) {
            // 此处捕获到了 token 过期所抛出的 TokenExpiredException 异常，我们在这里需要做的是刷新该用户的 token 并将它添加到响应头中
            try {
                $token = $this->auth->refresh();
                //替换当前验证头信息，保证完成本次请求
                $request->headers->add(['authorization' => 'Bearer ' . $token]);

                $user = $request->user();
                $this->withToken($request, $token, $user);
            } catch (JWTException $e) {
                throw new UnauthorizedHttpException('jwt-auth', $e->getMessage(), $e, $e->getCode());
            }
        }

        $response = $next($request);

        // Send the refreshed token back to the client.
        return $this->setAuthenticationHeader($response, $token);
    }

    protected function withToken($request, $token, $user)
    {
        $ip = $request->getClientIp(); // 用户ip
        
        $jwt_ttl = Auth::guard('api')->factory()->getTTL(); // 获取token过期时间
        $header_user_agent = $request->header()['user-agent'][0]; // 用户浏览器信息
     
        // 制作 token
        $key = 'singleToken:uId:'.$user->id;
        $singleToken = md5($ip . $header_user_agent . $user->id);
        Cache::put($key, $singleToken, $jwt_ttl);
    }
}
