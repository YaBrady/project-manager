<?php

namespace App\Http\Controllers\Api;

use App\Models\File;
use App\Transformers\UserTransformer;
use App\Http\Controllers\BaseController;
use Dingo\Api\Http\Response;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    /**
     * 个人信息
     *
     * @return Response
     */
    public function me()
    {
        $user = $this->user();
        return $this->response->item($user, new UserTransformer());
    }

    /**
     * 更新用户信息
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $user = $this->user();
        if (isset($request->password)) {
            $password       = sha1($request->password);
            $user->password = $password;
        }
        isset($request->name) ? $user->name = $request->name : null;
        if (isset($request->file_id)) {
            $file         = File::find($request->file_id);
            $user->avatar = $file->url;
        }
        $user->save();
        return $this->response()->array(['user' => [
            'avatar' => $user->avatar
        ]]);
    }
}
