<?php


namespace App\Traits;

use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Auth;

trait JWTHelper
{
    use Helpers;
    public function user()
    {
        return Auth::guard()->user();
    }
}