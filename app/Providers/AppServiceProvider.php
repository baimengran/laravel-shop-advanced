<?php

namespace App\Providers;

use App\Http\ViewComposers\CategoryTreeComposer;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Yansongda\Pay\Pay;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //当laravel渲染products.index和products.show模板时，就会使用CategoryTreeComposer类来注入类目树变量
        //同时laravel还支持通配符，理由products.*即代表当渲染products目录下的模板时，都执行这个ViewComposer
        \View::composer(['products.index', 'products.show'], CategoryTreeComposer::class);

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //往容器中注入一个名为alipay的单例对象
        $this->app->singleton('alipay', function () {
            $config = config('pay.alipay');
            //测试路由
            //$config['notify_url'] = 'http://requestbin.fullcontact.com/150q8oh1';//取得服务器回调参数测试
            $config['notify_url'] = route('payment.alipay.notify');//服务器回调
            $config['return_url'] = route('payment.alipay.return');//前端回调
            //判断当前项目运行环境是否为线上环境
            //app()->environment() 获取当前运行的环境，线上环境会返回 production。
            //对于支付宝，如果项目运行环境不是线上环境，则启用开发模式，并且将日志级别设置为 DEBUG。
            //由于微信支付没有开发模式，所以仅仅将日志级别设置为 DEBUG。
            if (app()->environment() !== 'production') {
                $config['mode'] = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            //调用yansongda\Pay来创建一个支付宝支付对象
            return Pay::alipay($config);
        });

        //创建一个名为wechat_pay的单例对象
        $this->app->singleton('wechat_pay', function () {
            $config = config('pay.wechat');
            $config['notify_url'] = 'http://xxx';//微信支付取得服务器回调参数测试
            $config['notify_url'] = route('payment.wechat.notify');
            //判断当前项目是否运行咋线上环境
            if (app()->environment() !== 'production') {
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            //调用Yansongda\Pay来创建一个微信支付对象
            return Pay::wechat($config);
        });
    }
}
