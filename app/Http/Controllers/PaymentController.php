<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Installment;
use App\Models\InstallmentItem;
use App\Models\Order;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /**
     * 分期付款支付
     * @param Order $order
     * @param Request $request
     * @return Installment
     * @throws InvalidRequestException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function payByInstallment(Order $order, Request $request)
    {
        //判断订单是否属于当前用户
        $this->authorize('view', $order);
        //订单已支付或已关闭
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }
        //校验用户提交的还款月数，数值必须是我们配置好费率的期数
        $this->validate($request, [
            'count' => ['required', Rule::in(array_keys(config('app.installment_fee_rate')))],
        ]);
        //删除同一笔商品订单发起过其他的状态是未支付的分期付款，避免同一笔商品订单有多个分期付款
        Installment::query()
            ->where('order_id', $order->id)
            ->where('status', Installment::STATUS_PENDING)
            ->delete();
        $count = $request->input('count');
        //创建一个新的分期付款对象
        $installment = new Installment([
            //总金额，既商品订单总金额
            'total_amount' => $order->total_amount,
            //分期期数
            'count' => $count,
            //从配置文件中读取响应期数的费率
            'fee_rate' => config('app.installment_fee_rate')[$count],
            //从配置文件中读取当期逾期费率
            'fine_rate' => config('app.installment_fine_rate'),
        ]);
        $installment->user()->associate($request->user());
        $installment->order()->associate($order);
        $installment->save();

        //第一期的还款截止日期为明天凌晨0点
        $dueDate = Carbon::tomorrow();
        //计算每一期的本金
        $base = big_number($order->total_amount)->divide($count)->getValue();
        //计算每一期的手续分
        $fee = big_number($base)->multiply($installment->fee_rate)->divide(100)->getValue();
        //根据用户选择的还款期数，创建对应数量的还款计划
        for ($i = 0; $i < $count; $i++) {
            //最后一期的本金需要用总本金减去前面几期的本金
            if ($i === $count - 1) {
                $base = big_number($order->total_amount)->subtract(big_number($base)->multiply($count - 1));
            }
            $installment->items()->create([
                'sequence' => $i,
                'base' => $base,
                'fee' => $fee,
                'due_date' => $dueDate,
            ]);
            //还款截止日期加30天
            $dueDate = $dueDate->copy()->addDays(30);
        }
        return $installment;

    }


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

    /**
     * 微信退款
     * @param Request $request
     * @return string
     */
    public function wechatRefundNotify(Request $request)
    {
        //给微信的失败响应
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><trtuen_msg><![CDATA[FAIL]]></trtuen_msg></xml>';
        $data = app('wechat_pay')->verify(null, true);
        //没有找到对应的订单，（保证代码健壮性）
        if (!$order = Order::query()->where('no', $data['out_trade_no'])->first()) {
            return $failXml;
        }

        if ($data['refund_status'] === 'SUCCESS') {
            //退款成功，将订单退款状态改为退款成功
            $order->update([
                'refund_status' => Order::REFUND_STATUS_SUCCESS,
            ]);
        } else {
            //退款失败
            $extra = $order->extra;
            $extra['refund_failed_code'] = $data['refund_status'];
            $order->update([
                'refund_status' => Order::REFUND_STATUS_FAILED,
            ]);
        }
        return app('wechat_pay')->success();
    }
}
