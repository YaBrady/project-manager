<?php


namespace App\Http\Services;


use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    /**
     * 获取用户所有属于的项目
     *
     * @param User $user
     * @param int $getMyProject
     * @return Collection
     */
    public function getAllProjects(User $user, $getMyProject = 0)
    {
        $projectMatesCst = $user->projectMate()->with('project')->distinct()->select('project_id', 'is_creator');
        if ($getMyProject) {
            // 单单是获取我是创建者的团队
            $projectMatesCst->where('is_creator', 1);
        }
        $projectMates = $projectMatesCst->get();

        foreach ($projectMates as $projectMate) {
            $project = $projectMate->project ?? new \stdClass();
            if (!empty($project)) {
                $user                = User::find($project->user_id);
                $projectMate->user_name = $user->name;
                $projectMate->avatar = $user->avatar;
            }
            $projectMate->project_name  = $project->project_name;
            $projectMate->desc       = $project->desc;
            $projectMate->created_at = $project->created_at->toDateString();
            unset($projectMate->project);
        }
        return $projectMates;
    }
}