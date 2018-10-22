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

class InstallmentsController extends Controller
{
    //
    /**
     * 分期付款列表页
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $installments = Installment::query()
            ->where('user_id', $request->user()->id)
            ->paginate(10);

        return view('installments.index', ['installments' => $installments]);
    }

    /**
     * 分期付款详情页
     * @param Installment $installment
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Installment $installment)
    {
        //取出当前分期付款的所有还款计划，并按还款顺序排列
        $item = $installment->items()->orderBy('sequence')->get();

        return view('installments.show', [
            'installment' => $installment,
            'items' => $item,
            //下一个未完成还款的还款计划
            'nextItem' => $item->where('paid_at', null)->first(),
        ]);
    }

    /**
     * 分期付款支付宝支付
     * @param Installment $installment
     * @return mixed
     * @throws InvalidRequestException
     */
    public function payByAlipay(Installment $installment)
    {

        if ($installment->order->closed) {
            throw new InvalidRequestException('对应商品订单已关闭');
        }
        if ($installment->status === Installment::STATUS_FINISHED) {
            throw new InvalidRequestException('该分期订单已结清');
        }
        //获取当前分期付款最后的一个未支付的还款计划
        if (!$nextItem = $installment->items()->whereNull('paid_at')->orderBy('sequence')->first()) {
            //如果没有未支付的还款，
            throw new InvalidRequestException('该分期订单已结清');
        }

        //调用支付宝的网页支付
        return app('alipay')->web([
            //支付订单号使用分期流水号+还款计划号
            'out_trade_no' => $installment->no . '_' . $nextItem->sequence,
            'total_amount' => $nextItem->total,
            'subject' => '支付Laravel Shop 的分期订单:' . $installment->no,
            //此处return_url和notify_url可以覆盖掉在AppServiceProvider设置的回调地址
            'return_url' => route('installments.alipay.return'),//前端回调
            'notify_url' => ngrok_url('installments.alipay.notify')//后台回调
        ]);


    }

    /**
     * 分期付款微信支付
     * @param Installment $installment
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws InvalidRequestException
     */
    public function payByWechat(Installment $installment)
    {
        if ($installment->order->closed) {
            throw new InvalidRequestException('对应的商品订单已被关闭');
        }
        if ($installment->status === Installment::STATUS_FINISHED) {
            throw new InvalidRequestException('该分期订单已结清');
        }
        if (!$nextItem = $installment->items()->whereNull('paid_at')->orderBy('sequence')->first()) {
            throw new InvalidRequestException('该分期订单已结清');
        }

        $wechatOrder = app('wechat_pay')->scan([
            'out_trade_no' => $installment->no . '_' . $nextItem->sequence,
            'total_fee' => $nextItem->total * 100,
            'body' => '支付 Laravel Shop 的分期订单：' . $installment->no,
            'notify_url' => ngrok_url('installments.wechat.notify'), // 微信支付后台回调url
        ]);
        // 把要转换的字符串作为 QrCode 的构造函数参数
        $qrCode = new QrCode($wechatOrder->code_url);

        // 将生成的二维码图片数据以字符串形式输出，并带上相应的响应类型
        return response($qrCode->writeString(), 200, ['Content-Type' => $qrCode->getContentType()]);
    }

    /**
     * 支付宝前端回调
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function alipayReturn()
    {
        try {
            app('alipay')->verify();
        } catch (\Exception $exception) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        return view('pages.success', ['msg' => '付款成功']);
    }

    /**
     * 支付宝支付后台回调
     * @return string
     */
    public function alipayNotify()
    {
        //校验支付宝回调参数是否正确
        $data = app('alipay')->verify();
       // \Log::debug($data->trade_no);
        if ($this->paid($data->out_trade_no, 'alipay', $data->trade_no)) {
            return app('alipay')->success();
        }
        return 'fail';
    }

    /**
     * 微信后台回调
     * @return string
     */
    public function wechatNotify()
    {
        $data = app('wechat_pay')->verify();
        if ($this->paid($data->out_trade_no, 'wechat', $data->transaction_id)) {
            return app('wechat_pay')->success();
        }

        return 'fail';
    }


    /**
     * 支付回调逻辑
     * @param $outTradeNo
     * @param $paymentMethod
     * @param $paymentNo
     * @return bool
     */
    public function paid($outTradeNo, $paymentMethod, $paymentNo)
    {
        //拉起支付时使用的支付订单号是由分期流水号+还款计划编号组成的
        //因此可以通过支付订单号来还原出这笔还款时哪个分期付款的哪个还款计划
        list($no, $sequence) = explode('_', $outTradeNo);
        //根据分期流水号查询对应的分期记录，原则上不会找不到，这里判断增加代码健壮性
        if (!$installment = Installment::query()->where('no', $no)->first()) {
            return false;
        }
        //根据还款计划编号查询对应的还款计划，原则上不会找不到，这里判断增加代码健壮性
        if (!$item = $installment->items()->where('sequence', $sequence)->first()) {
            return false;
        }
        //如果这个还款计划的支付状态是已支付，则告知支付宝此订单已完成，并不再执行后续逻辑
        if ($item->paid_at) {
            return true;
        }
        //更新对应的还款计划
        $item->update([
            'paid_at' => Carbon::now(),
            'payment_method' => $paymentMethod,
            'payment_no' => $paymentNo,
        ]);
        //如果这是第一笔还款
        if ($item->sequence === 0) {
            //将分期付款的状态改为还款中
            $installment->update(['status' => Installment::STATUS_REPAYING]);
            //将分期付款对应的商品订单状态改为已支付
            $installment->order->update([
                'paid_at' => Carbon::now(),
                'payment_method' => 'installment',//支付方式为分期付款
                'payment_no' => $no,//支付订单号为分期付款的流水号
            ]);

            //触发商品订单已支付的事件
            event(new OrderPaid($installment->order));
        }

        //如果只是最后一笔还款
        if ($item->sequence === $installment->count - 1) {
            //将分期付款状态改为已结清
            $installment->update(['status' => Installment::STATUS_FINISHED]);
        }
        return true;
    }

    /**
     * 微信退款回调
     * @param Request $request
     * @return string
     */
    public function wechatRefundNotify(Request $request)
    {
        //给微信的失败响应
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        //校验微信回调参数
        $data = app('wechat_pay')->verify(null, true);
        //根据单号拆分出对应的商品退款单号及对应的还款计划号
        list($no, $sequence) = explode('_', $data['out_refund_no']);

        $item = InstallmentItem::query()
            ->whereHas('installment', function ($query) use ($no) {
                $query->whereHas('order', function ($query) use ($no) {
                    $query->where('refund_no', $no);//根据订单表的退款流水号找到对应还款计划
                });
            })->where('sequence', $sequence)->first();

        //没有找到对应的订单，原则上不可能，为了代码健壮性
        if (!$item) {
            return $failXml;
        }
        //如果退款成功
        if ($data['refund_status'] === 'SUCCESS') {
            //将还款计划退款状态改成退款成功
            $item->update([
                'refund_status' => InstallmentItem::REFUND_STATUS_SUCCESS,
            ]);
            $item->installment->refreshRefundStatus();
        } else {
            //否则将对应还款计划的退款状态改为退款失败
            $item->update([
                'refund_status' => InstallmentItem::REFUND_STATUS_FAILED,
            ]);
        }
        return app('wechat_pay')->success();
    }
}
