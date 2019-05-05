<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace'  => 'App\Http\Controllers',
    'middleware' => ['serializer:array']
], function ($api) {
    $api->group([
        'middleware' => 'api.throttle', // 调用频率限制的中间件
        'limit'      => 600, // 允许次数
        'expires'    => 1, // 分钟
    ], function ($api) {
        // 测试请求
        $api->get('version', function () {
            return response('this is version v1');
        });

//        //TODO:记得删除
//        $api->get('test/user', function () {
//            if (app()->environment() == 'local' || app()->environment() == 'testing') {
//                $user  = \App\Models\User::find(1);
//                $token = \Illuminate\Support\Facades\Auth::guard()->fromUser($user);
//                // 添加 token
//                $jwt_ttl     = Illuminate\Support\Facades\Auth::guard()->factory()->getTTL();
//                $key         = 'singleToken:uId:' . $user->id;
//                $singleToken = md5(get_client_ip() . $_SERVER['HTTP_USER_AGENT'] . $user->id);
//                Illuminate\Support\Facades\Cache::put($key, $singleToken, $jwt_ttl);
//                return response()->json(['access_token' => $token]);
//            }
//        });

        // 登陆
        $api->post('login', 'Auth\LoginController@login');

        // 注册
        $api->post('register', 'Auth\LoginController@register');

        // 需要 token 验证的接口
        $api->group([
            'namespace' => 'Api',
            'middleware' => ['refresh.token']],
            function ($api) {
            // 当前登录用户信息
            $api->get('user', 'UserController@me');

            /**
             * 团队
             */

                // 获取团队
                $api->get('team', 'TeamController@getTeam');

        });
    });
});
