<?php

namespace App\Http\Requests;

use App\Models\ProductSku;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends Request
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //判断用户提交的地址ID是否存在于数据库，并属于当前用户
            //where条件防止恶意用户用不同地址ID不断提交订单来遍历出平台所有用户的收货地址
            //rule:exists 验证user_addresses表的id字段是否与输入相同，并当前用户id与匹配的user_addresses表user_id相同
            'address_id' => ['required', Rule::exists('user_addresses', 'id')->where('user_id', $this->user()->id)],
            'items' => ['required', 'array'],
            'items.*.sku_id' => [
                //检查items数组下每一个子数组的sku_id
                'required',
                //闭包验证，[参数名、参数值、错误回调]
                function ($attribute, $value, $fail) {
                    if (!$sku = ProductSku::find($value)) {
                        $fail($sku->title.'商品不存在');
                        return;
                    }
                    if (!$sku->product->on_sale) {
                        $fail('该商品未上架');
                        return;
                    }
                    if ($sku->stock === 0) {
                        $fail('该商品以售完');
                        return;
                    }
                    //获取当前索引($attribute 相当于items.0.sku_id)
                    preg_match('/items\.(\d+)\.sku_id/', $attribute, $m);
                    $index = $m[1];
                    //根据索引找到用户所提交的购买数量
                    $amount = $this->input('items')[$index]['amount'];
                    if ($amount > 0 && $amount > $sku->stock) {
                        $fail($sku->product->title.' '.$sku->title.'商品库存不足');
                        return;
                    }
                },
            ],
            'items.*.amount' => ['required', 'integer', 'min:1'],

        ];
    }
}
