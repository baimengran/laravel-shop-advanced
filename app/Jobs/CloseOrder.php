<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class CloseOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order, $delay)
    {
        //
        $this->order = $order;
        //设置延迟时间，delay()方法的参数代表多少秒之后执行
        $this->delay($delay);
    }

    /**
     * Execute the job.
     * 当队列任务处理器从队列中去除任务时，会调用handle()方法
     * @return void
     */
    public function handle()
    {
        //判断对应的订单是否已经被支付
        //如果已经支付则不需要关闭订单，直接退出
        if ($this->order->paid_at) {
            return;
        }

        //通过事务执行SQL
        DB::transaction(function () {
            //将订单的closed字段标记为true，即关闭订单
            $this->order->update(['closed' => true]);
            //循环遍历订单中的商品SKU，将订单中的数量加回到SKU的库存中去
            foreach ($this->order->items as $item) {
                $item->productSku->addStock($item->amount);
            }
            if ($this->order->couponCode) {
                $this->order->couponCode->changeUsed(false);
            }
        });

    }
}
