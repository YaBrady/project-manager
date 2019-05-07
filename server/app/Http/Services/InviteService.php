<?php


namespace App\Http\Services;


use App\Http\Requests\Request;
use App\Models\Invite;

class InviteService
{
    /**
     * 获取用户所有的邀请信息
     * @param Request $request
     * @param null $user
     */
    public function getAllInvites(Request $request,$user = null)
    {
        // 获取所有邀请
        $inviteOrm = Invite::UserInvites($user->id);

        $invites = $inviteOrm->get();
        // 将邀请置为已看
        $inviteOrm->update(['receive_status'=>1]);
    }
}