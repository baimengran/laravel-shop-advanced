<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //支付宝回调服务器RUL
        'payment/alipay/notify',
        //微信支付服务器回调url
        'payment/wechat/notify',
        //微信退款回调路由
        'payment/wechat/refund_notify',
        //分期付款支付宝后台回调URL
        'installments/alipay/notify',
        //分期付款微信后台回调URL
        'installments/wechat/notify',
        //微信退款
        'installments/wechat/refund_notify',
    ];
}
