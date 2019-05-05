<?php

namespace App\Http\Controllers;

class BaseController extends Controller
{
    /**
     * 错误返回
     * @param $message
     * @param $error_code
     * @param array $extra
     * @param int $status_code
     * @return mixed
     */
    public function myerror($message, $error_code = 400, $extra = [], $status_code = 422)
    {
        $arr['message'] = $message;
        if ($error_code != 400) {
            $arr['errors']['errcode'] = $error_code;
            if (is_int($extra)) {
                $status_code = $extra;
                $extra = is_array($status_code) ?  $status_code : [];
            }
            $arr['errors'] += $extra;

        } else {
            $status_code = $error_code;
        }
        $arr['status_code'] = $status_code;
        return $this->response->array($arr)->setStatusCode($status_code);
    }
}