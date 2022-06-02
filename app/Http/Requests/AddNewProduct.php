<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddNewProduct extends FormRequest
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
            'productfoldername' => 'required',
            'productname' => 'required',
            'forumlink' => 'required',
            'listingpageimagelink' => 'required',
            'listingpagedesc' => 'required',
            'SortOrder' => 'required',
            'listingpageimagelink' => 'required',
        ];
    }
}
