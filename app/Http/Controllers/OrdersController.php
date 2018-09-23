<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    //
    public function store(OrderRequest $request)
    {
        //通过验证器获取当前登录用户
        $user = $request->user();
        //开启一个数据库事务
        $order = DB::transaction(function () use ($user, $request) {
            //查询地址
            $address = UserAddress::find($request->input('address_id'));
            //更新此地址的最后使用时间
            $address->update(['last_used_at' => Carbon::now()]);//Carbon日期处理实例，继承子php原生DataTime
            //创建一个订单
            $order = new Order([
                'address' => [
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => $request->input('remark'),
                'total_amount' => 0,
            ]);
            //订单关联到当前用户,associate从属关系添加
            $order->user()->associate($user);
            //写入数据库
            $order->save();

            $totalAmount = 0;
            $items = $request->input('items');
            //遍历用户提交商品SKU
            foreach ($items as $data) {
                $sku = ProductSku::find($data['sku_id']);
                //创建一个OrderItem并直接与当前订单关联
                //通过与orderItems关联关系查询数据集合并用make()方法创建一个新的集合实例，
                //等同于 $item = new OrderItem(); $item->order()->associate($order);
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price,
                ]);
                //从属关系添加商品id
                $item->product()->associate($sku->product_id);
                //从属关系添加商品SKU信息
                $item->productSku()->associate($sku);
                $item->save();
                //累计计算订单商品总价
                $totalAmount += $sku->price * $data['amount'];
                //减库存
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }

            //更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            //将下单的商品从购物车中移除
            //collect辅助函数创建集合，并利用集合方法pluck()方法通过给定键获取所有集合值
            $skuIds = collect($request->input('items'))->pluck('sku_id');
            //将本次订单商品SKU从购物车删除，
            $user->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
            return $order;
        });

        return $order;

    }
}
