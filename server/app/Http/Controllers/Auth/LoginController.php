<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Transformers\UserTransformer;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Overtrue\LaravelWeChat\Facade as EasyWeChat;

class LoginController extends BaseController
{
    /**
     * @param LoginRequest $request
     * @throws \ErrorException
     */
    public function login(LoginRequest $request)
    {
        $email         = $request->email;
        $password      = $request->password;
        $encryptedPass = sha1($password);
        $user          = User::where('email', $email)->first();
        try {
            if (empty($user) || $user->password !== $encryptedPass) {
                throw new Exception('请输入正确的邮箱或者密码');
            }
        } catch (\Exception $exception) {
            return $this->response()->errorUnauthorized($exception->getMessage());
        }
        $token        = Auth::guard()->login($user);
        $meta         = $this->respondWithToken($request, $token, $user);
        $data['meta'] = $meta;
        return $this->response->array($data)->setStatusCode(201);
    }


    /**
     * 注册
     *
     * @param LoginRequest $request
     * @throws \ErrorException
     */
    public function register(LoginRequest $request)
    {
        $email    = $request->email;
        $password = $request->password;
        $name = $request->name;
        $user     = User::where('email', $email)->first();
        if ($user) {
            return $this->response()->error('用户已存在');
        }
        $user = new User();
        $user->email = $email;
        $user->name = $name;
        $user->password = sha1($password);
        $user->save();
        $token        = Auth::guard()->login($user);
        $meta         = $this->respondWithToken($request, $token, $user);
        $data['meta'] = $meta;
        return $this->response->array($data)->setStatusCode(201);
    }


    /**
     * 生成响应内容
     *
     * @param $token
     * @return mixed
     * @throws \ErrorException
     */
    protected function respondWithToken($request, $token, $user)
    {
        $ip = $request->getClientIp(); // 用户ip

        $jwt_ttl           = Auth::guard()->factory()->getTTL(); // 获取token过期时间
        $header_user_agent = $request->header()['user-agent'][0]; // 用户浏览器信息

        // 制作 token
        $key         = 'singleToken:uId:' . $user->id;
        $singleToken = md5($ip . $header_user_agent . $user->id);
        Cache::put($key, $singleToken, $jwt_ttl);
        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => Auth::guard()->factory()->getTTL()
        ];
    }

}
