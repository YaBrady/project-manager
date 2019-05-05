<?php

namespace App\Http\Controllers;

use App\Traits\JWTHelper;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use JWTHelper;
}
