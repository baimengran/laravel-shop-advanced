<?php

namespace App\Console\Commands\Cron;

use App\Jobs\RefundCrowdfundingrder;
use App\Models\CrowdfundingProduct;
use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FinishCrowdFunding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:finish-crowdfunding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '结束众筹';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        CrowdfundingProduct::query()
            //预加载商品数据
            ->with(['product'])
            //众筹结束时间早于当前时间
            ->where('end_at', '<=', Carbon::now())
            //众筹状态为众筹中
            ->where('status', CrowdfundingProduct::STATUS_FUNDING)
            ->get()
            ->each(function (CrowdfundingProducts $crowdfundingProducts) {
                //如果众筹目标金额大于实际众筹金额
                if ($crowdfundingProducts->target_amount > $crowdfundingProducts->total_amount) {
                    //调用众筹失败逻辑
                    $this->crowdfundingFailed($crowdfundingProducts);
                } else {
                    //否则调用众筹成功逻辑
                    $this->crowdfundingSucceed($crowdfundingProducts);
                }
            });
    }

    /**
     * 众筹成功
     * @param CrowdfundingProduct $crowdfundingProduct
     */
    protected function crowdfundingSucceed(CrowdfundingProduct $crowdfundingProduct)
    {
        //将众筹状态改为众筹成功
        $crowdfundingProduct->update([
            'status' => CrowdfundingProduct::STATUS_SUCCESS,
        ]);
    }

    /**
     * 众筹失败
     * @param CrowdfundingProduct $crowdfundingProduct
     */
    protected function crowdfundingFailed(CrowdfundingProduct $crowdfundingProduct)
    {
        //将众筹状态改为失败
        $crowdfundingProduct->update([
            'status' => CrowdfundingProduct::STATUS_FAIL,
        ]);
        dispatch(new RefundCrowdfundingrder($crowdfundingProduct));
    }
}
