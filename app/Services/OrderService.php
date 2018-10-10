<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/24
 * Time: 12:43
 */

namespace App\Services;


use App\Exceptions\CouponCodeUnavailableException;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use App\Models\CouponCode;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;


class OrderService
{

    public function store(User $user, UserAddress $address, $remark, $items, CouponCode $coupon = null)
    {
        //如果传入了优惠卷，先检查是否可用
        if ($coupon) {
            $coupon->checkAvailable();
        }
        //开启一个事务
        $order = \DB::transaction(function () use ($user, $address, $remark, $items, $coupon) {
            //更新最后使用时间
            $address->update(['last_used_at' => Carbon::now()]);
            //创建一个订单
            $order = new Order([
                'address' => [//将地址信息以json存入订单中
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => $remark,
                'total_amount' => 0,
            ]);
            //订单关联到当前用户
            $order->user()->associate($user);
            //写入数据库
            $order->save();

            $totalAmount = 0;
            //遍历用户提交的SKU
            foreach ($items as $data) {
                $sku = ProductSku::find($data['sku_id']);
                //创建一个OrderItem并直接与当前订单关联
                //make()方法会在集合基础上创建新的集合实例
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price,
                ]);

                //从属关系添加商品id
                $item->product()->associate($sku->product_id);
                //从属关系添加商品SKU信息
                $item->productSku()->associate($sku);
                //写入数据库
                $item->save();

                //计算总价
                $totalAmount += $sku->price * $data['amount'];
                //减库存
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }

            //使用优惠卷优惠规则
            if ($coupon) {
                //验证总金额是否符合优惠卷规则
                $coupon->checkAvailable($totalAmount);
                //把订单金额修改为优惠后的金额
                $totalAmount = $coupon->getAdjustedPrice($totalAmount);
                //将订单与优惠卷关联
                $order->couponCode()->associate($coupon);
                //增加优惠卷的用量，需判断返回值
                if ($coupon->changeUsed() <= 0) {
                    throw new CouponCodeUnavailableException('该优惠卷已被兑完');
                }
            }

            //更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            //将下单的商品从购物车中移除
            //collect辅助函数创建集合，并利用集合方法pluck()方法通过给定键获取所有集合值
            $skuIds = collect($items)->pluck('sku_id')->all();
            //store() 方法是我们手动调用的，无法通过 Laravel 容器的自动解析来注入。
            //在代码里调用封装的库时一定 不可以 使用 new 关键字来初始化，而是应该通过 Laravel 的容器来初始化，
            //因为在之后的开发过程中 CartService 类的构造函数可能会发生变化，比如注入了其他的类，
            //如果我们使用 new 来初始化的话，就需要在每个调用此类的地方进行修改；
            //而使用 app() 或者自动解析注入等方式 Laravel 则会自动帮我们处理掉这些依赖
            app(CartService::class)->remove($skuIds);
            return $order;
        });

        //dispatch()辅助函数分发关闭订单任务队列
        dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }
}