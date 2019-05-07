<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\BaseController;
use App\Http\Services\TeamService;
use App\Models\Invite;
use App\Models\Team;
use App\Models\TeamMates;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends BaseController
{
    /**
     * 获取团队详情
     *
     * @param Team $team
     * @return mixed
     */
    public function show(Team $team)
    {
        $teamDetail = $team->load('teamMate');
        $teamMate   = $teamDetail->teamMate;
        if (!empty($teamMate)) {
            foreach ($teamMate as $item) {
                $user            = User::find($item->user_id);
                $item->user_name = $user->name;
                $item->avatar    = $user->avatar;
            }
        }
        return $this->response()->array($teamDetail);
    }

    /**
     * 获取关联的团队
     *
     * @param TeamService $teamService
     * @return mixed
     */
    public function index(TeamService $teamService)
    {
        $user  = $this->user();
        $teams = $teamService->getAllTeams($user);
        return $this->response()->array([
            'teams' => $teams
        ]);
    }

    /**
     * 创建团队
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        if (!isset($request->team_name)) {
            return $this->myerror('缺少团队名');
        }
        $user            = $this->user();
        $team            = new Team();
        $team->team_name = $request->team_name;
        $team->desc      = $request->desc ?? '';
        $user->teams()->save($team);
        $teamMate             = new TeamMates();
        $teamMate->is_creator = 1;
        $teamMate->user_id    = $user->id;
        $teamMate->team_name  = $request->team_name;
        $team->teamMate()->save($teamMate);
        return $this->response()->array([
            'team_id' => $team->id,
            'created_at'=>$team->created_at->toDateString(),
            'user_name'=>$user->name,
        ]);
    }

    /**
     * 更新团队
     * @param Team $team
     * @param Request $request
     * @return mixed
     */
    public function update(Team $team, Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->user();
            $user->hasThisTeam($team->id);
            isset($request->team_name) ? $team->team_name = $request->team_name : null;
            isset($request->desc) ? $team->desc = $request->desc : null;
            $team->save();
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->myerror($exception->getMessage());
        }
        return $this->response()->array(['message' => 'ok']);
    }

    /**
     * 删除团队
     * @param Team $team
     * @return mixed
     * @throws \Exception
     */
    public function destroy(Team $team)
    {
        DB::beginTransaction();
        try {
            $user = $this->user();
            $user->hasThisTeam($team->id);
            $team->teamMate()->delete();
            $team->delete();
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->myerror($exception->getMessage());
        }
        return $this->response()->array(['message' => 'ok']);
    }


    /**
     * 团队邀请新成员
     *
     * @param Team $team
     * @param Request $request
     * @return mixed
     */
    public function inviteMate(Team $team, Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->user();
            $user->hasThisTeam($team->id);

            // 已有的团员,去重
            $teamMate = $team->teamMate()->with('user')->get();
            $emails   = $request->emails;
            foreach ($emails as $index => $email) {
                foreach ($teamMate as $item) {
                    if ($item->user->email === $email) {
                        unset($emails[$index]);
                    }
                }
            }
            $validateUsers = User::whereIn('email', $emails)->get();
            foreach ($validateUsers as $validateUser) {
                // 发起邀请
                $invite                 = new Invite();
                $invite->user_id        = $user->id;
                $invite->invite_user_id = $validateUser->id;
                $invite->user_name      = $user->name;
                $invite->invitable_name = $team->team_name;
                $team->invites()->save($invite);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->myerror($exception->getMessage());
        }
        return $this->response()->array(['message' => 'ok']);
    }

    /**
     * 移除队员
     *
     * @param Team $team
     * @param Request $request
     * @return mixed
     */
    public function deleteMate(Team $team, Request $request)
    {
        if (!$request->userid) {
            return $this->myerror('缺少userid字段');
        }
        $user = $this->user();
        if ($request->userid != $user->id) {
            $user->hasThisTeam($team->id);
            $teamMate = $team->teamMate()->where('user_id', $request->userid);
            if ($teamMate) {
                $teamMate->delete();
            }

        }
        return $this->response()->array(['message' => 'ok']);
    }
}