<?php


namespace App\Http\Services;


use App\Models\User;

class TeamService
{
    /**
     * 获取用户所有属于的团队
     *
     * @param User $user
     * @param int $getMyTeam
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTeams(User $user, $getMyTeam = 0)
    {
        $teamMatesCst = $user->teamMate()->with('team')->select('team_id', 'is_creator');
        if ($getMyTeam) {
            // 单单是获取我是创建者的团队
            $teamMatesCst->where('is_creator', 1);
        }
        $teamMates = $teamMatesCst->get();
        foreach ($teamMates as $teamMate) {
            $team = $teamMate->team ?? new \stdClass();
            if (!empty($team)) {
                $user                = User::find($team->user_id);
                $teamMate->user_name = $user->name;
                $teamMate->avatar = $user->avatar;
            }
            $teamMate->team_name  = $team->team_name;
            $teamMate->desc       = $team->desc;
            $teamMate->created_at = $team->created_at->toDateString();
            unset($teamMate->team);
        }
        return $teamMates;
    }
}