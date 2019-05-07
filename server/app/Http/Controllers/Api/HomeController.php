<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\BaseController;
use App\Http\Requests\Request;
use App\Http\Services\InviteService;
use App\Http\Services\TeamService;
use App\Models\Invite;

class HomeController extends BaseController
{

    /**
     * 获取首页数据
     * @return
     */
    public function home()
    {
        $user = $this->user();
        // 获取所有邀请
        $inviteOrm = Invite::select('id','user_name','invitable_name')->UserInvites($user->id)->orderByDesc('id');
        $invites = $inviteOrm->get();
        // 将邀请置为已看
//        $inviteOrm->update(['receive_status'=>1]);

        // 获取团队
        return $this->response()->array([
            'invites'=>$invites,
        ]);
    }
}