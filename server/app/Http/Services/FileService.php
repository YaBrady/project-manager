<?php


namespace App\Http\Services;


use Carbon\Carbon;

class FileService
{
    /*
    * 生成随机文件名
    */
    private function generateFileName()
    {
        return md5(uniqid() . time());
    }

    /**
     * 保存文件
     *
     * @param $file
     * @return array
     */
    public function save($file)
    {
        $asoPath  = 'upload/images/' . Carbon::now()->toDateString();
        $savePath = public_path($asoPath);
        if (!is_dir($savePath)) {
            mkdir($savePath);
        }
        $fileName = $this->generateFileName() . '.' . $file->getClientOriginalExtension();
        $file->move($savePath, $fileName);
        return ["$savePath/$fileName", url("$asoPath/$fileName"),];
    }

    /**
     * 删除文件
     *
     * @param $file
     */
    public function delete($file)
    {
        if(file_exists($file->path)){
            unlink($file->path);
        }
    }

}