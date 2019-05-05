<?php

namespace App\Http\Controllers\Api;

use App\Transformers\UserTransformer;
use App\Http\Controllers\BaseController;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Auth;

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
}
