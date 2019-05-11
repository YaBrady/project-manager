<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\BaseController;
use App\Http\Services\FileService;
use App\Models\comment;
use App\Models\File;
use App\Models\Project;
use App\Models\ProjectItem;
use Illuminate\Http\Request;

class ProjectItemController extends BaseController
{

    /**
     * 创建一个条目
     *
     * @param Project $project
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function store(Project $project, Request $request)
    {
        $user = $this->user();
        try {
            if (!isset($request->item_name) && !isset($request->project_id)) {
                throw new \Exception('请求参数丢失');
            }
            $user->isJoinProject($project->id);
            $item            = new ProjectItem();
            $item->item_name = $request->item_name;
            $item->user_id   = $user->id;
            $item->status    = $request->status ?? 0;
            $project->items()->save($item);
            return $this->response()->array(['item_id' => $item->id, 'time' => $item->created_at->toDateString()]);
        } catch (\Exception $exception) {
            return $this->myerror($exception->getMessage());
        }
    }

    /**
     * 删除条目
     *
     * @param Project $project
     * @param ProjectItem $item
     * @return mixed
     */
    public function destroy(Project $project, ProjectItem $item)
    {
        $user = $this->user();
        try {
            $user->isJoinProject($project->id);
            $item->delete();
            return $this->response()->array(['message' => 'ok']);

        } catch (\Exception $exception) {
            return $this->myerror($exception->getMessage());
        }
    }


    /**
     * 更新条目
     *
     * @param Project $project
     * @param ProjectItem $item
     * @param Request $request
     * @return mixed
     */

    public function update(Project $project, ProjectItem $item, Request $request)
    {
        $user = $this->user();
        try {
            $user->isJoinProject($project->id);
            $response = ['message' => 'ok'];
            isset($request->item_name) ? $item->item_name = $request->item_name : null;
            isset($request->desc) ? $item->desc = $request->desc : null;
            isset($request->start_time) ? $item->start_time = $request->start_time : null;
            isset($request->end_time) ? $item->end_time = $request->end_time : null;
            isset($request->important) ? $item->important = $request->important : null;
            isset($request->status) ? $item->status = $request->status : null;
            if (isset($request->file_id)) {
                $file = File::find($request->file_id);
                $item->files()->save($file);
                $response['file'] = [
                    'url'         => $file->url,
                    'origin_name' => $file->origin_name,
                    'created_at'  => $file->created_at->toDateTimeString(),
                    'name'        => $user->name,
                    'id'          => $file->id,
                ];
            }
            $item->save();
            return $this->response()->array($response);
        } catch (\Exception $exception) {
            return $this->myerror($exception->getMessage());
        }
    }

    /**
     * 获取条目详情
     *
     * @param Project $project
     * @param ProjectItem $item
     * @return mixed
     */
    public function show(Project $project, ProjectItem $item)
    {
        $user = $this->user();
        try {
            $user->isJoinProject($project->id);

            $item->time = $item->created_at->toDateString();
            $comments   = $item->comments()->with('user')->select('user_id', 'id', 'comment', 'created_at')->orderByDesc('created_at')->get();
            foreach ($comments as $comment) {
                $comment->avatar = $comment->user->avatar;
                $comment->name   = $comment->user->name;
                unset($comment->user);
            }
            $files = $item->files()->with('user')->select('id', 'origin_name', 'url', 'created_at', 'user_id')->orderByDesc('created_at')->get();
            foreach ($files as $file) {
                $file->avatar = $file->user->avatar;
                $file->name   = $file->user->name;
                unset($file->user);
            }
            $lists      = $item->lists()->select('id', 'list_content', 'status')->get();
            $finishList = 0;
            foreach ($lists as $list) {
                if ($list->status == 1) {
                    $finishList++;
                }
                $list->status = "{$list->status}";
            }
            $item->comments   = $comments;
            $item->files      = $files;
            $item->lists      = $lists;
            $item->finishList = $finishList;
            return $this->response()->array(['item' => $item]);
        } catch (\Exception $exception) {
            return $this->myerror($exception->getMessage());
        }
    }

    /**
     * 首页获取所有条目
     *
     * @param Project $project
     * @return mixed
     */
    public function index(Project $project)
    {
        $user = $this->user();
        try {
            $user->isJoinProject($project->id);

            $items = $project->items()->select('id', 'item_name', 'created_at', 'status',
                'end_time')->orderByDesc('created_at')->get();
            $list  = [];
            foreach ($items as $item) {
                if (!isset($list[$item->status])) {
                    $list[$item->status] = [];
                }
                // 获取评论，获取文件数目，获取步骤以及完成度
                $dateString = $item->created_at->toDateString();
                unset($item->created_at);
                $item->time        = $dateString;
                $item->comment_sum = $item->comments()->count();
                $item->file_sum    = $item->files()->count();
                $item->list_sum    = $item->lists()->count();
                $item->list_finish = $item->lists()->where('status', 1)->count();
                array_push($list[$item->status], $item);
            }
            $undoList  = $list[0] ?? [];
            $preList   = $list[1] ?? [];
            $doingList = $list[2] ?? [];
            $endList   = $list[3] ?? [];

            return $this->response()->array([
                'undoList'  => $undoList,
                'preList'   => $preList,
                'doingList' => $doingList,
                'endList'   => $endList
            ]);
        } catch (\Exception $exception) {
            return $this->myerror($exception->getMessage());
        }
    }

    /**
     * 留言
     *
     * @param Project $project
     * @param ProjectItem $item
     * @param Request $request
     * @return mixed
     */
    public function storeComment(Project $project, ProjectItem $item, Request $request)
    {
        $user = $this->user();
        try {
            $user->isJoinProject($project->id);
            if (!isset($request->comment)) {
                throw new \Exception('请求参数丢失');
            }
            $comment          = new Comment();
            $comment->comment = $request->comment;
            $comment->user_id = $user->id;
            $item->comments()->save($comment);
            return $this->response()->array(['comment_id' => $comment->id, 'time' => $comment->created_at->toDateTimeString(),'name'=>$user->name,'avatar'=>$user->avatar]);
        } catch (\Exception $exception) {
            return $this->myerror($exception->getMessage());
        }
    }

    /**
     * 删除评论
     *
     * @param Project $project
     * @param comment $comment
     * @return mixed
     */
    public function deleteComment(Project $project, Comment $comment)
    {
        $user = $this->user();
        try {
            $user->isJoinProject($project->id);
            $comment->delete();
            return $this->response()->array(['message' => 'ok']);
        } catch (\Exception $exception) {
            return $this->myerror($exception->getMessage());
        }
    }

    /**
     * 删除附件
     *
     * @param Project $project
     * @param ProjectItem $item
     * @param Request $request
     * @param FileService $fileService
     * @return mixed
     */
    public function deleteFile(Project $project, ProjectItem $item, Request $request, FileService $fileService)
    {
        $user = $this->user();
        try {
            $user->isJoinProject($project->id);
            $files = $request->fileids;
            if (!$request->files) {
                throw new \Exception('缺少参数');
            }


            foreach ($files as $file) {
                $file = $item->files()->find($file);
                if (!$file) {
                    throw new \Exception('该文件不属于该条目');
                }
                $item->files()->delete($file);
                $fileService->delete($file);
            }
            return $this->response()->array(['message' => 'ok']);
        } catch (\Exception $exception) {
            return $this->myerror($exception->getMessage());
        }
    }
}