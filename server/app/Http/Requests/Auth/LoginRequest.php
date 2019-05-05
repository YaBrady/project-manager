<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Api\FormRequest;
use App\Http\Requests\Request;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->is('api/login')) {
            return [
                'email' => 'required|email',
                'password' => 'required|string',
            ];
        }
        if ($this->is('api/register')) {
            return [
                'email' => 'required|email',
                'password' => 'required|string',
            ];
        }
        return [];
    }

    public function messages()
    {
        return [
            'email.required' => 'email必须传入',
            'email.email' => 'email传入非法',
            'password.required' => 'password参数必须传入',
            'password.string' => 'password参数非法',
        ];
    }
}
