<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\BaseController;
use App\Http\Services\ProjectService;
use App\Models\Invite;
use App\Models\Project;
use App\Models\ProjectMate;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends BaseController
{

    /**
     * 获取项目详情
     *
     * @param Project $project
     * @return mixed
     */
    public function show(Project $project)
    {
        $projectMate = $project->projectMate()->with('team', 'user')->orderByDesc('is_creator')->get();
        // 关联的团队列表
        $teams      = [];
        $teamsIndex = [];
        // 受邀人员
        $invites = [];
        if (!empty($projectMate)) {
            foreach ($projectMate as $item) {
                if ($item->team_id) {
                    // 具有团队
                    if (!isset($teams[$item->team_id])) {
                        $team                  = $item->team;
                        $teams[$item->team_id] = [
                            'team_id'   => $item->team_id,
                            'team_name' => $team->team_name
                        ];
                        array_push($teamsIndex, $item->team_id);
                    }
                } else {
                    // 若是特别邀请人员
                    if ($item->is_invite || $item->is_creator) {
                        $user = $item->user;
                        array_push($invites, [
                            'user_id'    => $user->id,
                            'user_name'  => $user->name,
                            'avatar'     => $user->avatar,
                            'is_creator' => $item->is_creator,
                        ]);
                    }
                }

            }
        }
        // 提取值
        $teams = array_values($teams);
        return $this->response()->array([
            'teams'      => $teams,
            'teamsIndex' => $teamsIndex,
            'invites'    => $invites
        ]);
    }

    /**
     * 新建项目
     *
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        if (!isset($request->project_name)) {
            return $this->myerror('缺少团队名');
        }
        $user                  = $this->user();
        $project               = new Project();
        $project->project_name = $request->project_name;
        $project->desc         = $request->desc ?? '';
        $user->projects()->save($project);
        $projectMate             = new ProjectMate();
        $projectMate->is_creator = 1;
        $projectMate->user_id    = $user->id;
        $projectMate->created_at = $project->created_at;

        $project->projectMate()->save($projectMate);
        return $this->response()->array([
            'project_id' => $project->id,
            'created_at' => $project->created_at->toDateString(),
            'user_name'  => $user->name,
        ]);
    }

    /**
     * 更新项目
     *
     * @param Project $project
     * @param Request $request
     * @return mixed
     */
    public function update(Project $project, Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->user();
            $user->hasThisProject($project->id);
            isset($request->project_name) ? $project->project_name = $request->project_name : null;
            isset($request->desc) ? $project->desc = $request->desc : null;
            $project->save();
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->myerror($exception->getMessage());
        }
        return $this->response()->array(['message' => 'ok']);
    }

    /**
     * 获取关联的项目
     *
     * @param ProjectService $projectService
     * @param Request $request
     * @return mixed
     */
    public function index(ProjectService $projectService, Request $request)
    {
        $user         = $this->user();
        $getMyProject = $request->only_my_project ?? 0;
        $projects     = $projectService->getAllProjects($user, $getMyProject);
        return $this->response()->array([
            'projects' => $projects
        ]);
    }

    /**
     * 删除项目
     * @param Project $project
     * @return mixed
     */
    public function destroy(Project $project)
    {
        DB::beginTransaction();
        try {
            $user = $this->user();
            $user->hasThisProject($project->id);
            $project->invites()->where('status', 0)->update(['status' => 3]);
            $project->projectMate()->delete();
            $project->items()->delete();
            $project->delete();
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->myerror($exception->getMessage());
        }
        return $this->response()->array(['message' => 'ok']);
    }

    /**
     * 项目邀请新成员
     *
     * @param Project $project
     * @param Request $request
     * @return mixed
     */
    public function inviteMate(Project $project, Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->user();
            $user->hasThisProject($project->id);

            // 已有的团员,去重
            $projectMate = $project->projectMate()->with('user')->get();
            $emails      = $request->emails;
            foreach ($emails as $index => $email) {
                foreach ($projectMate as $item) {
                    if ($item->user->email === $email && $item->is_invite == 1) {
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
                $invite->invitable_name = $project->project_name;
                $project->invites()->save($invite);
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
     * @param Project $project
     * @param Request $request
     * @return mixed
     */
    public function deleteMate(Project $project, Request $request)
    {
        if (!$request->userid) {
            return $this->myerror('缺少userid字段');
        }
        $user = $this->user();
        if ($request->userid != $user->id) {
            $user->hasThisProject($project->id);
            $projectMate = $project->projectMate()->where('user_id', $request->userid);
            if ($projectMate) {
                $projectMate->delete();
            }
        }
        return $this->response()->array(['message' => 'ok']);
    }

    /**
     * 将我的团队关联到项目
     *
     * @param Project $project
     * @param Team $team
     * @return mixed
     */
    public function relateTeam(Project $project, Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->user();
            $user->hasThisProject($project->id);
            $teams = $request->teams ?? [];

            // 将项目关联的团队队员全部删除
            $project->projectMate()->whereNotNull('team_id')->delete();

            $teams = Team::whereIn('id', $teams)->get();
            foreach ($teams as $team) {
                $user->hasThisTeam($team->id);
                // 将团队成员解析出来
                $teamMate = $team->teamMate()->get();
                foreach ($teamMate as $item) {
                    if ($item->user_id === $user->id) {
                        continue;
                    }
                    $mates             = new ProjectMate();
                    $mates->team_id    = $team->id;
                    $mates->user_id    = $item->user_id;
                    $mates->created_at = Carbon::now();
                    $project->projectMate()->save($mates);
                }
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->myerror($exception->getMessage());
        }
        return $this->response()->array(['message' => 'ok']);
    }



}
