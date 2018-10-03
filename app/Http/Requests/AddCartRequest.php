<?php

namespace App\Http\Requests;

use App\Models\ProductSku;
use Illuminate\Foundation\Http\FormRequest;

class AddCartRequest extends Request
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
            'sku_id' => [
                'required',
                //闭包验证，[参数名、参数值、错误回调]
                function ($attribute, $value, $fail) {
                    if (!$sku = ProductSku::find($value)) {
                        $fail('该商品不存在');
                        return;
                    }
                    if (!$sku->product->on_sale) {
                        $fail('该商品未上架');
                        return;
                    }
                    if ($sku->stock === 0) {
                        $fail('该商品已售完');
                        return;
                    }
                    if ($this->input('amount') > 0 && $sku->stock < $this->input('amount')) {
                        $fail('该商品库存不足');
                        return;
                    }
                },
            ],
            'amount' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * 修改参数显示中文
     * @return array
     */
    public function attributes()
    {
        return [
            'amount' => '商品数量'
        ];
    }

    /**
     * 重写验证错误信息
     * @return array
     */
    public function messages()
    {
        return [
            'sku_id.required' => '请选择商品'
        ];
    }
}