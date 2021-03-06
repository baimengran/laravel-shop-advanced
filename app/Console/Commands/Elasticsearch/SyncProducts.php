<?php

namespace App\Console\Commands\Elasticsearch;

use App\Models\Product;
use Illuminate\Console\Command;

class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:sync-products {--index=products}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将商品数据同步到Elasticsearch';


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
        //获取Elasticearch对象
        $es = app('es');
        Product::query()
            //预加载 SKU 和商品属性数据，避免N+1问题
            ->with(['skus', 'properties'])
            //使用chunkById避免一次性加载过多数据
            ->chunkById(100, function ($products) use ($es) {
                $this->info(sprintf('正同步 ID 范围 %s 至 %s 的商品', $products->first()->id, $products->last()->id));

                //初始化请求体
                $req = ['body' => []];
                //遍历商品
                foreach ($products as $product) {
                    $data = $product->toESArray();

                    $req['body'][] = [
                        'index' => [
                            //从参数中读取索引
                            '_index' => $this->option('index'),
                            '_type' => '_doc',
                            '_id' => $data['id'],
                        ],
                    ];
                    $req['body'][] = $data;
                }
                try {
                    //使用bulk方法批量创建
                    //Elasticsearch批量操作接口
                    $es->bulk($req);
                } catch (\Exception $exception) {
                    $this->error($exception->getMessage());
                }
            });
    }
}
