<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\BaseController;
use App\Http\Requests\Api\FileRequest;
use App\Http\Services\FileService;
use App\Models\File;
use Illuminate\Support\Facades\DB;

class FileController extends BaseController
{

    /**
     * 保存文件
     *
     * @param FileRequest $request
     * @param FileService $fileService
     * @return mixed
     */
    public function upload(FileRequest $request, FileService $fileService)
    {
        DB::beginTransaction();
        try {
            $file              = $request->file;
            $dbFile            = new File();
            $user              = $this->user();
            $dbFile->user_id   = $user->id;
            $dbFile->file_type = $file->getMimeType();
            // 保存文件
            list($realPath, $url) = $fileService->save($file);
            $dbFile->url  = $url;
            $dbFile->path = $realPath;
            $dbFile->origin_name = $file->getClientOriginalName();
            $dbFile->save();
            DB::commit();
            return $this->response()->array(['file_id' => $dbFile->id]);
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->myerror('文件上传失败');
        }
    }

    /**
     * 删除文件
     *
     * @param File $file
     * @param FileService $fileService
     * @return mixed
     * @throws \Exception
     */
    public function delete(File $file, FileService $fileService)
    {
        $fileService->delete($file);
        $file->delete();
        return $this->response()->array(['message' => 'ok']);
    }
}