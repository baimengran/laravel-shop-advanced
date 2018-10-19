<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Installment;
use Carbon\Carbon;
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


    public function alipayNotify()
    {
        //校验支付宝回调参数是否正确
        $data = app('alipay')->verify();
        //拉起支付时使用的支付订单号是由分期流水号+还款计划编号组成的
        //因此可以通过支付订单号来还原出这笔还款时哪个分期付款的哪个还款计划
        list($no, $sequence) = explode('_', $data->out_trade_no);
        //根据分期流水号查询对应的分期记录，原则上不会找不到，这里判断增加代码健壮性
        if (!$installment = Installment::query()->where('no', $no)->first()) {
            return 'fail';
        }
        //根据还款计划编号查询对应的还款计划，原则上不会找不到，这里判断增加代码健壮性
        if (!$item = $installment->items()->where('sequence', $sequence)->first()) {
            return 'fail';
        }
        //如果这个还款计划的支付状态是已支付，则告知支付宝此订单已完成，并不再执行后续逻辑
        if ($item->paid_at) {
            return app('alipay')->success();
        }
        //更新对应的还款计划
        $item->update([
            'paid_at' => Carbon::now(),
            'payment_method' => 'alipay',
            'payment_to' => $data->trade_no,
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
        return app('alipay')->success();
    }

}
