<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    //
    public function add(AddCartRequest $request){
        //获取当前登录用户
        $user = $request->user();
        //获取添加购物车商品SKU的id
        $skuId = $request->input('sku_id');
        //获取添加购物车商品的数量
        $amount = $request->input('amount');

        //从数据库中查询该商品是否已经在购物车中
        if($cart = $user->cartItems()->where('product_sku_id',$skuId)->first()){
        //如果存在则直接叠加商品数量
            $cart->update([
                'amount'=>$cart->amount+$amount,
            ]);
        }else{
            //否则创建一个新的购物记录
            $cart = new CartItem(['amount'=>$amount]);
            //belongTo关系更新
            $cart->user()->associate($user);
            $cart->productSku()->associate($skuId);
            $cart->save();
        }
        return[];
    }
}
