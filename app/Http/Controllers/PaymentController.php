<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    //
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
     * 分发事件
     * @param Order $order
     */
    public function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }
}
