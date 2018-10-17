<?php

namespace App\Admin\Controllers;

use App\Exceptions\InternalException;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\HandleRefundRequest;
use App\Http\Requests\Request;
use App\Models\CrowdfundingProduct;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class OrdersController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('订单列表')
            //->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show(Order $order, Content $content)
    {
        return $content
            ->header('查看订单')
            //->description('description')
            ->body(view('admin.orders.show', ['order' => $order]));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order);

        //只展示已经支付的订单，并默认按支付时间倒序排列
        $grid->model()->whereNotNull('paid_at')->orderBy('paid_at', 'desc');
        //$grid->id('Id');
        $grid->no('订单流水号');
        //展示关联关系的字段时，使用column方法
        $grid->column('user.name', '买家');
        //$grid->user_id('User id');
        //$grid->address('Address');
        $grid->total_amount('总金额')->sortable();
        //$grid->remark('Remark');
        $grid->paid_at('支付时间')->sortable();
        //$grid->payment_method('Payment method');
        //$grid->payment_no('Payment no');
        $grid->refund_status('退款状态')->display(function ($value) {
            return Order::$refundStatusMap[$value];
        });
        //$grid->refund_no('Refund no');
        //$grid->closed('Closed');
        //$grid->reviewed('Reviewed');
        $grid->ship_status('物流')->display(function ($value) {
            return Order::$shipStatusMap[$value];
        });
        //$grid->ship_data('Ship data');
        //$grid->extra('Extra');
        //$grid->created_at('Created at');
        //$grid->updated_at('Updated at');

        //禁用创建按钮，
        $grid->disableCreateButton();
        //禁用删除和编辑按钮
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });
        //禁用批量删除按钮
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Order::findOrFail($id));

        $show->id('Id');
        $show->no('No');
        $show->user_id('User id');
        $show->address('Address');
        $show->total_amount('Total amount');
        $show->remark('Remark');
        $show->paid_at('Paid at');
        $show->payment_method('Payment method');
        $show->payment_no('Payment no');
        $show->refund_status('Refund status');
        $show->refund_no('Refund no');
        $show->closed('Closed');
        $show->reviewed('Reviewed');
        $show->ship_status('Ship status');
        $show->ship_data('Ship data');
        $show->extra('Extra');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order);

        $form->text('no', 'No');
        $form->number('user_id', 'User id');
        $form->textarea('address', 'Address');
        $form->decimal('total_amount', 'Total amount');
        $form->textarea('remark', 'Remark');
        $form->datetime('paid_at', 'Paid at')->default(date('Y-m-d H:i:s'));
        $form->text('payment_method', 'Payment method');
        $form->text('payment_no', 'Payment no');
        $form->text('refund_status', 'Refund status')->default('pending');
        $form->text('refund_no', 'Refund no');
        $form->switch('closed', 'Closed');
        $form->switch('reviewed', 'Reviewed');
        $form->text('ship_status', 'Ship status')->default('pending');
        $form->textarea('ship_data', 'Ship data');
        $form->textarea('extra', 'Extra');

        return $form;
    }


    public function ship(Order $order, Request $request)
    {
        //判断当前订单是否以支付
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付');
        }
        //判断当前订单发货状态是否为未发货
        if ($order->ship_status !== Order::SHIP_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已发货');
        }

        //众筹订单只有在众筹成功之后才能发货
        if ($order->type === Order::TYPE_CROWDFUNDING &&
            $order->items[0]->product->crowdfunding->status !== CrowdfundingProduct::STATUS_SUCCESS) {
            throw new InvalidRequestException('众筹订单只能在众筹成功之后才能发货');
        }

        //laravel5.5之后validate方法可以返回检验过的值
        $data = $this->validate($request, [
            'express_company' => ['required'],
            'express_no' => ['required'],
        ], [], [
            'express_company' => '物流公司',
            'express_no' => '物流单号',
        ]);

        //将订单发货状态改为以发货，并存入物流信息
        $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            //Order模型的$casts属性中指明了ship_data是一个数组
            'ship_data' => $data,
        ]);
        //返回上一页
        return redirect()->back();
    }

    /**
     * 退款处理
     * @param Order $order
     * @param HandleRefundRequest $request
     * @return Order
     * @throws InvalidRequestException
     */
    public function handleRefund(Order $order, HandleRefundRequest $request)
    {
        //判断订单状态是否正确
        if ($order->refund_status !== Order::REFUND_STATUS_APPLIED) {
            throw new InvalidRequestException('订单状态不正确');
        }
        //同意退款
        if ($request->input('agree')) {
            //调用退款逻辑
            $this->_refundOrder($order);
        } else {
            //拒绝退款理由放到订单的extra字段中
            $extra = $order->extra ? '' : [];
            $extra['refund_disagree_reason'] = $request->input('reason');
            //将订单的退款状态改为未退款
            $order->update([
                'refund_status' => Order::REFUND_STATUS_PENDING,
                'extra' => $extra,
            ]);
        }
        return $order;
    }

    /**
     * 退款动作函数
     * @param Order $order
     * @throws InternalException
     */
    public function _refundOrder(Order $order)
    {
        //判断支付方式
        switch ($order->payment_method) {
            case 'wechat':
                //微信
                //生成退款订单号
                $refundNo = Order::getAvailableRefundNo();
                app('wechat_pay')->refund([
                    'out_trade_no' => $order->no,//订单流水号
                    'total_fee' => $order->total_amount * 100,//原订单金额
                    'refund_fee' => $order->total_amount * 100,//要退款的订单金额
                    'out_refund_no' => $refundNo,//退款订单号
                    //微信支付的退款结果不是事实返回，而是通过退款回调来通知，因此需要配上退款回调接口地址
                    'notify_url' => route('payment.wechat.refund_notify'),//
                ]);
                //将订单状态改为退款中
                $order->update([
                    'refund_no' => $refundNo,
                    'refund_status' => Order::REFUND_STATUS_PROCESSING,
                ]);
                break;
            case 'alipay':
                //支付宝
                //调用退款订单号生成函数
                $refundNo = Order::getAvailableRefundNo();
                //调用支付宝实例的refund方法
                $ref = app('alipay')->refund([
                    'out_trade_no' => $order->no,//订单流水号
                    'refund_amount' => $order->total_amount,//退款金额，单位元
                    'out_request_no' => $refundNo,//退款订单号
                ]);

                //根据支付宝的文档，如果返回值里有sub_code字段说明退款失败
                if ($ref->sub_code) {
                    //将退款失败的返回结果保存如extra字段
                    $extra = $order->extra;
                    $extra['refund_failed_code'] = $ref->sub_code;
                    //将订单的退款状态标记为退款失败
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_FAILED,
                        'extra' => $extra,
                    ]);
                } else {
                    //退款成功
                    //将订单的退款状态标记为退款成功，并保存退款订单号
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_SUCCESS,
                    ]);
                }
                break;
            default:
                //原则上不可能出现其他情况，此代码为了代码的健壮性
                throw new InternalException('未知订单支付方式：' . $order->payment_method);
                break;
        }
    }
}
