<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendReviewRequest extends Request
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'reviews' => ['required', 'array'],
            'reviews.*.id' => [
                'required',
                //判断数组id是否存在于order_items表，并order_items表的order_id与指定路由参数order对象参数id相同（$this->route('order')可以获取指定路由参数对象）
                Rule::exists('order_items', 'id')->where('order_id', $this->route('order')->id)
            ],
            'reviews.*.rating' => ['required', 'integer', 'between:1,5'],
            'reviews.*.review' => ['required'],
        ];
    }


    public function attributes()
    {
        // return parent::attributes(); // TODO: Change the autogenerated stub
        return [
            'reviews.*.rating' => '评分',
            'reviews.*.review' => '评价',
        ];
    }
}
