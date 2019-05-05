<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use App\Models\User;

use App\Http\Controllers\BaseController;

class PhoneSms extends BaseController
{
    /**
     * [作用]判断此手机号码是否已给绑定
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!$request['phone']){
            return $this->myerror('手机号码不能为空');
        }
        
        $user = Auth::guard('api')->user();  
        if($user['phone']){
            return $this->myerror('该用户已绑定手机号码');
        }

        $user_phone_number = User::where('phone', '=', $request['phone'])->count();
        if($user_phone_number){
            return $this->myerror('此号码已给绑定');
        }

        return $next($request);
    }
}
