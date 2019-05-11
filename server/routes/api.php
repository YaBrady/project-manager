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
             * 邀请
             */

            // 接受/拒绝邀请 --完成
            $api->put('invites/{invite}', 'InviteController@modify');
            // 分页邀请列表 --完成
            $api->get('invites', 'InviteController@all');
            // 获取弹窗邀请列表 --完成
            $api->get('notifyInvites', 'InviteController@getNotifyInvites');


            /**
             * 项目
             */

            // 项目资源 --完成
            $api->resource('projects', 'ProjectController');

            // 邀请项目 --完成
            $api->post('projects/{project}/project_mate', 'ProjectController@inviteMate');

            // 移除项目成员 --完成
            $api->delete('projects/{project}/project_mate', 'ProjectController@deleteMate');

            // 项目关联团队 --完成
            $api->put('projects/{project}/teams','ProjectController@relateTeam');

            /**
             * 项目条目
             */

            // 条目资源
            $api->resource('projects/{project}/items', 'ProjectItemController');

            // 步骤资源
            $api->resource('projects/{project}/items/{item}/lists', 'ProjectItemListController');

            // 新增评论
            $api->post('projects/{project}/items/{item}/comment', 'ProjectItemController@storeComment');
            // 删除评论
            $api->delete('projects/{project}/comments/{comment}', 'ProjectItemController@deleteComment');
            // 删除附件
            $api->delete('projects/{project}/items/{item}/files','ProjectItemController@deleteFile');
            /**
             * 文件
             */
            // 上传文件 √
            $api->post('file', 'FileController@upload');
            // 删除文件
            $api->delete('files/{file}', 'FileController@delete');

        });
    });
});
