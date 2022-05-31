<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadsRequest extends FormRequest
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
            'productfamily' => 'required',
            'product' => 'required',
            // 'folder' => 'required',
            'title' => 'required',
            'description' => 'required',
            'releaseurl' => 'required',
            'file' => 'required',
            
        ];
    }
}
