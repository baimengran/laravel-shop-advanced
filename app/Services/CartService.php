<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/24
 * Time: 12:09
 */

namespace App\Services;

use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

/**
 * 购物车封装
 * Class CartService
 * @package App\Services
 */
class CartService
{

    /**
     * 获取商品及商品SKU
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get()
    {
        return Auth::user()->cartItems()->with('productSku.product')->get();
    }

    /**
     * 创建购物车
     * @param $skuId 商品SKU ID
     * @param $amount 商品数量
     * @return CartItem|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null
     */
    public function add($skuId, $amount)
    {
        $user = Auth::user();
        //从数据库中查询该商品是否已经在购物册中
        if ($item = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
            //如果存在则直接叠加商品数量
            $item->update([
                'amount' => $item->amount + $amount,
            ]);
        } else {
            //否则创建一个新的购物车记录
            $item = new CartItem(['amount' => $amount]);
            $item->user()->associate($user);
            $item->productSku()->associate($skuId);
            $item->save();
        }
        return $item;
    }

    /**
     * 删除购物车商品
     * @param $skuIds 商品SKU ID
     */
    public function remove($skuIds)
    {
        //可以传单个ID也可以传ID数组
        if (!is_array($skuIds)) {
            $skuIds = [$skuIds];
        }
        Auth::user()->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
    }

}