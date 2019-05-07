<?php


namespace App\Http\Requests\Api;


class FileRequest extends FormRequest
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
        return [
            'file' => 'required|max:50000',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'file必须传入',
            'file.max' => '文件尺寸太大',
        ];
    }
}