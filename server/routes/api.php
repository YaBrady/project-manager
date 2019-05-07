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
    'middleware' => ['serializer:array', 'bindings']
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

        // 登陆 √
        $api->post('login', 'Auth\LoginController@login');
        // 注册 √
        $api->post('register', 'Auth\LoginController@register');

        // 需要 token 验证的接口
        $api->group([
            'namespace'  => 'Api',
            'middleware' => ['refresh.token']
        ], function ($api) {

            // 当前登录用户信息 √
            $api->get('user', 'UserController@me');
            // 注销 √
            $api->post('logout', '\App\Http\Controllers\Auth\LoginController@logout');
            // 更新用户信息 √
            $api->post('user', 'UserController@update');

            /**
             * 团队
             */

            $api->resource('teams', 'TeamController');

            /**
             * 团队成员
             */

            // 邀请团队 --完成
            $api->post('teams/{team}/team_mate', 'TeamController@inviteMate');

            // 移除队员 --完成
            $api->delete('teams/{team}/team_mate', 'TeamController@deleteMate');

            /**
             * 网页首页
             */

            $api->get('home', 'HomeController@home');


            /**
             * 邀请
             */

            // 接受/拒绝邀请 --完成
            $api->put('invites/{invite}', 'InviteController@modify');
            // 分页邀请列表 --完成
            $api->get('invites', 'InviteController@all');


            /**
             * 项目
             */

            // 项目资源


            /**
             * 文件
             */
            // 上传文件 √
            $api->post('file','FileController@upload');
            // 删除文件
            $api->delete('files/{file}','FileController@delete');

        });
    });
});
