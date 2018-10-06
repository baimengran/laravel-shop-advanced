<?php

namespace App\Listeners;

use App\Events\OrderReviewed;
use App\Models\OrderItem;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;


class UpdateProductRating implements ShouldQueue
{


    /**
     * Handle the event.
     *
     * @param  OrderReviewed $event
     * @return void
     */
    public function handle(OrderReviewed $event)
    {

        //通过with方法预加载数据，
        $items = $event->getOrder()->items()->with(['product'])->get();

        foreach ($items as $item) {
            //商品id与当前订单商品id相同
            $result = OrderItem::query()->where('product_id', $item->product_id)
                //订单是已经支付的
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('paid_at');
                })->first([
                    //first() 方法接受一个数组作为参数，代表此次 SQL 要查询出来的字段，
                    //默认情况下 Laravel 会给数组里面的值的两边加上 ` 这个符号，比如 first(['name', 'email']) 生成的 SQL
                    // 会类似：select `name`, `email` from xxx
                    //  如果直接传入 first(['count(*) as review_count', 'avg(rating) as rating'])，
                    //最后生成的 SQL 肯定是不正确的。这里用 DB::raw() 方法来解决这个问题，
                    //Laravel 在构建 SQL 的时候如果遇到 DB::raw() 就会把 DB::raw() 的参数原样拼接到 SQL 里
                    \DB::raw('count(*) as review_count'),
                    \DB::raw('avg(rating) as rating')
                ]);
            $item->product->update([
                'rating' => $result->rating,
                'review_count' => $result->review_count,
            ]);
        }

    }

    public function failed($exception)
    {
        \Log::debug($exception);
    }
}
