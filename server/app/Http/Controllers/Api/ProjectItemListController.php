<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\BaseController;
use App\Models\Project;
use App\Models\ProjectItem;
use App\Models\ProjectItemList;
use Illuminate\Http\Request;

class ProjectItemListController extends BaseController
{
    /**
     * 创建步骤
     *
     * @param Project $project
     * @param ProjectItem $item
     * @param Request $request
     * @return mixed
     */
    public function store(Project $project, ProjectItem $item, Request $request)
    {
        $user = $this->user();
        try {
            $user->isJoinProject($project->id);

            if (!$request->list_content) {
                throw new \Exception('缺少参数');
            }
            $list = new ProjectItemList();
            $list->list_content = $request->list_content;
            $list->user_id = $user->id;
            $item->lists()->save($list);
            return $this->response()->array(['list_id' => $list->id]);
        } catch (\Exception $exception) {
            return $this->myerror($exception->getMessage());
        }
    }

    /**
     * 更新条目/
     *
     * @param Project $project
     * @param ProjectItem $item
     * @param ProjectItemList $list
     * @param Request $request
     * @return mixed
     */
    public function update(Project $project, ProjectItem $item,ProjectItemList $list, Request $request)
    {
        $user = $this->user();
        try {
            $user->isJoinProject($project->id);

            isset($request->list_content) ?  $list->list_content = $request->list_content: null;
            isset($request->status) ?  $list->status = $request->status: null;
            $list->save();
            return $this->response()->array(['message' => 'ok']);
        } catch (\Exception $exception) {
            return $this->myerror($exception->getMessage());
        }
    }

    /**
     * 删除条目
     *
     * @param Project $project
     * @param ProjectItem $item
     * @param ProjectItemList $list
     * @return mixed
     */
    public function destroy(Project $project, ProjectItem $item,ProjectItemList $list)
    {
        $user = $this->user();
        try {
            $user->isJoinProject($project->id);
            $list->delete();
            return $this->response()->array(['message' => 'ok']);
        } catch (\Exception $exception) {
            return $this->myerror($exception->getMessage());
        }
    }



}