<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\BaseController;
use App\Models\Invite;
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

        $object = $invite->invitable()->first();
        if ($object instanceof Team) {

            // 若是团队邀请，将成员拉进团队
            if ($status == 1 && !$object->teamMate()->where('user_id', $user->id)->first()) {
                // 若还没有邀请
                $team_mates             = new TeamMates();
                $team_mates->user_id    = $user->id;
                $team_mates->is_creator = 0;
                $team_mates->created_at = Carbon::now();
                $team_mates->team_name = $object->team_name;
                $object->teamMate()->save($team_mates);
            }
            // 将该用户下的该团队邀请都置状态
            $object->invites()->where('invite_user_id', $user->id)->update(['status' => $status]);
        } else {
            // 若是项目邀请
        }
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
        $pageSize = 5;
        $user = $this->user();
        $pagination = Invite::where('invite_user_id',$user->id)->orderBy('status')->orderByDesc('created_at')->paginate($pageSize);
        foreach ($pagination as $item) {
            $item->isProjectInvite = 1;
            if($item->invitable_type === Team::class) {
                //若是团队邀请
                $item->isProjectInvite = 0;
            }
            unset($item->invitable_type);
        }
        return $this->response->array($pagination->toArray());
    }
}