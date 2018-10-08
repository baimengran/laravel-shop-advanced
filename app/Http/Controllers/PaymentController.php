<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * 支付宝支付
     * @param Order $order
     * @param Request $request
     * @return mixed
     * @throws InvalidRequestException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function payByAlipay(Order $order, Request $request)
    {
        //判断订单是否属于当前用户
        $this->authorize('view', $order);
        //订单已支付或者已关闭
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        //调用支付宝的网页支付
        return app('alipay')->web([
            'out_trade_no' => $order->no,//订单编号，需保证在商户端不重复
            'total_amount' => $order->total_amount,//订单金额，单位元，支付小数点后两位
            'subject' => '支付Laravel Shop的订单' . $order->no,//订单标题
        ]);
    }

    /**
     * 支付宝前端回调
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function alipayReturn()
    {
        try {
            //app('alipay')->verify() 用于校验提交的参数是否合法，支付宝的前端跳转会带有数据签名，
            //通过校验数据签名可以判断参数是否被恶意用户篡改。同时该方法还会返回解析后的参数
            $data = app('alipay')->verify();
            //dd($data);
        } catch (\Exception $e) {
            return view('pages.error', ['msg', '数据不正确']);
        }

        return view('pages.success', ['msg' => '付款成功']);
    }


    /**
     * 支付宝后台回调
     * @return string
     */
    public function alipayNotify()
    {   //校验输入参数
        $data = app('alipay')->verify();
        //\Log::debug('Alipay notify', $data->all()); 由于服务器端的请求我们无法看到返回值，
        //使用 dd 就不行了，所以需要通过日志的方式来保存
        //Log::debug('Alipay notify', $data->all());

        //$data->out_trade_no拿到订单流水号，并在数据库中查询
        $order = Order::where('no', $data->out_trade_no)->first();
        //判断支付订单是否存在，加强系统健壮性
        if (!$order) {
            return 'fail';
        }
        //如果订单状态以支付
        if ($order->paid_at) {
            //返回数据给支付宝
            return app('alipay')->success();
        }

        $order->update([
            'paid_at' => Carbon::now(),//支付时间
            'payment_method' => 'alipay',//支付方式
            'payment_no' => $data->trade_no,//支付宝订单号
        ]);

        //支付成功后调用OrderPaid事件分发函数
        $this->afterPaid($order);
        return app('alipay')->success();
    }

    /**
     * 支付成功后发送邮件、计算销量事件分发
     * @param Order $order
     */
    public function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }

    /**
     * 微信支付
     * @param Order $order
     * @param Request $request
     * @return mixed
     * @throws InvalidRequestException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function payByWechat(Order $order, Request $request)
    {
        //校验权限
        $this->authorize('view', $order);
        //校验订单状态
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单转台不正确');
        }

        //scan方法拉起微信扫码支付
        $wechatOrder = app('wechat_pay')->scan([
            'out_trade_no' => $order->no,//商户订单流水号，与支付宝一样
            'total_fee' => $order->total_amount * 100,//微信支付金额是分
            'body' => '支付Laravel Shop的订单:' . $order->no,//订单描述
        ]);
        //把要转换的字符串做诶QrCode的构造函数参数
        $qrcode = new QrCode($wechatOrder->code_url);
        //将生成的二维码图片数据以字符串形式输出，并带上响应的类型
        return response($qrcode->writeString(), 200, ['Content-type' => $qrcode->getContentType()]);
    }

    /**
     * 微信支付后台回调，（微信之后没有前段回调）
     * @return string
     */
    public function wechatNotify()
    {
        //校验回调参数是否正确
        $data = app('wechat_pay')->verify();
        //找到对应的订单
        $order = Order::query()->where('no', $data->out_trade_no)->first();
        //订单不存在则告知微信支付
        if (!$order) {
            return 'fail';
        }
        //订单已支付
        if ($order->paid_at) {
            //告知微信支付此订单已经处理
            return app('wechat_pay')->success();
        }
        //将订单标记为以支付
        $order->update([
            'paid_at' => Carbon::now(),
            'payment_method' => 'wechat',
            'payment_no' => $data->transaction_id,
        ]);
        return app('wechat_apy')->success();

    }
}
