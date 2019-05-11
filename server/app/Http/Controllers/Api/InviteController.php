<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\BaseController;
use App\Models\Invite;
use App\Models\ProjectMate;
use App\Models\Team;
use App\Models\TeamMates;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InviteController extends BaseController
{

    /**
     * 接受拒绝邀请
     *
     * @param Invite $invite
     * @param Request $request
     * @return mixed
     */
    public function modify(Invite $invite, Request $request)
    {
        $user = $this->user();
        if ($invite->status !== 0) {
            return $this->myerror('已处理过邀请');
        }
        $status = $request->status ?? 0;

        $object    = $invite->invitable()->first();
        $isTeam    = $object instanceof Team;
        $mateObj   = $isTeam ? $object->teamMate() : $object->projectMate();
        $hasRecord = $isTeam ? $mateObj->where('user_id', $user->id)->first() : $mateObj->where([
            ['user_id', $user->id],
            ['is_invite', 1]
        ])->first();

        if ($status == 1 && !$hasRecord) {
            // 若还没有邀请
            $mates = null;
            if ($isTeam) {
                // 多态
                $mates            = new TeamMates();
            } else {
                $mates               = new ProjectMate();
                $mates->is_invite   = 1;
            }
            $mates->user_id    = $user->id;
            $mates->is_creator = 0;
            $mates->created_at = Carbon::now();
            $mateObj->save($mates);
        }
        $object->invites()->where('invite_user_id', $user->id)->update(['status' => $status]);
        $invite->save();
        return $this->response()->array(['message' => 'ok']);
    }

    /**
     * 获取列表
     *
     * @return mixed
     */
    public function all()
    {
        $pageSize   = 5;
        $user       = $this->user();
        $pagination = Invite::where('invite_user_id',
            $user->id)->orderBy('status')->orderByDesc('created_at')->paginate($pageSize);
        foreach ($pagination as $item) {
            $item->isProjectInvite = 1;
            if ($item->invitable_type === Team::class) {
                //若是团队邀请
                $item->isProjectInvite = 0;
            }
            unset($item->invitable_type);
        }
        return $this->response->array($pagination->toArray());
    }

    /**
     * 获取弹窗邀请列表
     *
     * @return mixed
     */
    public function getNotifyInvites()
    {
        $user = $this->user();
        // 获取所有邀请
        $inviteOrm = Invite::select('id','user_name','invitable_name')->UserInvites($user->id)->orderByDesc('id');
        $invites = $inviteOrm->get();
        // 将邀请置为已看
        $inviteOrm->update(['receive_status'=>1]);

        // 获取团队
        return $this->response()->array([
            'invites'=>$invites,
        ]);
    }
}