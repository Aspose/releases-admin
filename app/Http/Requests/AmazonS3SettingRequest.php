<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AmazonS3SettingRequest extends FormRequest
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
            'bucketname' => 'required',
            'apikey' => 'required',
            'apisecret' => 'required',
            'hugositeurl' => 'required',
        ];
    }
}
