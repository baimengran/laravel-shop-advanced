<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyRefundRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'reason' => 'required',
        ];
    }

    public function attributes()
    {
        //return parent::attributes(); // TODO: Change the autogenerated stub
        return [
            'reason' => '原因',
        ];
    }
}
