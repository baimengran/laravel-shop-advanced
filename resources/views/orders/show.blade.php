@extends('layouts.app')
@section('title', '查看订单')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>订单详情</h4>
                </div>
                <div class="panel-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>商品信息</th>
                            <th class="text-center">单价</th>
                            <th class="text-center">数量</th>
                            <th class="text-right item-amount">小计</th>
                        </tr>
                        </thead>
                        @foreach($order->items as $index => $item)
                            <tr>
                                <td class="product-info">
                                    <div class="preview">
                                        <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">
                                            <img src="{{ $item->product->image_url }}">
                                        </a>
                                    </div>
                                    <div>
                                        <span class="product-title">
                                            <a target="_blank"
                                               href="{{ route('products.show', [$item->product_id]) }}">{{ $item->product->title }}</a>
                                        </span>
                                        <span class="sku-title">{{ $item->productSku->title }}</span>
                                    </div>
                                </td>
                                <td class="sku-price text-center vertical-middle">￥{{ $item->price }}</td>
                                <td class="sku-amount text-center vertical-middle">{{ $item->amount }}</td>
                                <td class="item-amount text-right vertical-middle">
                                    ￥{{ number_format($item->price * $item->amount, 2, '.', '') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="4"></td>
                        </tr>
                    </table>
                    <div class="order-bottom">
                        <div class="order-info">
                            {{--join(' ', $order->address) php函数，将数组元素转换成字符串--}}
                            <div class="line">
                                <div class="line-label">收货地址：</div>
                                <div class="line-value">{{ join(' ', $order->address) }}</div>
                            </div>
                            <div class="line">
                                <div class="line-label">订单备注：</div>
                                <div class="line-value">{{ $order->remark ?: '-' }}</div>
                            </div>
                            <div class="line">
                                <div class="line-label">订单编号：</div>
                                <div class="line-value">{{ $order->no }}</div>
                            </div>
                            {{--输出物流状态--}}
                            <div class="line">
                                <div class="line-label">物流状态：</div>
                                <div class="line-value">{{ \App\Models\Order::$shipStatusMap[$order->ship_status] }}</div>
                            </div>
                            <!-- 如果有物流信息则展示 -->
                            @if($order->ship_data)
                                <div class="line">
                                    <div class="line-label">物流信息：</div>
                                    <div class="line-value">{{ $order->ship_data['express_company'] }} {{ $order->ship_data['express_no'] }}</div>
                                </div>
                            @endif
                            {{--订单已支付，并退款状态不是未退款时，展示退款信息--}}
                            @if($order->paid_at&&$order->refund_status!==\App\Models\Order::REFUND_STATUS_PENDING)
                                <div class="line">
                                    <div class="line-label">退款状态：</div>
                                    <div class="line-value">{{\App\Models\Order::$refundStatusMap[$order->refund_status]}}</div>
                                </div>
                                <div class="line">
                                    <div class="line-label">退款理由：</div>
                                    <div class="line-value">{{$order->extra['refund_reason']}}</div>
                                </div>
                            @endif
                        </div>
                        <div class="order-summary text-right">
                            {{--展示优惠卷信息开始--}}
                            @if($order->couponCode)
                                <div class="text-primary">
                                    <span>优惠信息：</span>
                                    <div class="value">{{$order->couponCode->description}}</div>
                                </div>
                            @endif
                            {{--展示优惠卷信息结束--}}
                            <div class="total-amount">
                                <span>订单总价：</span>
                                <div class="value">￥{{ $order->total_amount }}</div>
                            </div>
                            <div>
                                <span>订单状态：</span>
                                <div class="value">
                                    @if($order->paid_at)
                                        @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                                            已支付
                                        @else
                                            {{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}
                                        @endif
                                    @elseif($order->closed)
                                        已关闭
                                    @else
                                        未支付
                                    @endif
                                </div>
                            </div>
                            {{--拒绝退款开始--}}
                            @if(isset($order->extra['refund_disagree_reason']))
                                <div>
                                    <span>拒绝退款理由：</span>
                                    <div class="value">{{$order->extra['refund_disagree_reason']}}</div>
                                </div>
                            @endif
                            {{--拒绝退款结束--}}
                            {{--支付开始--}}
                            @if(!$order->paid_at&&!$order->closed)
                                <div class="payment-buttons">
                                    <a class="btn btn-primary btn-sm"
                                       href="{{route('payment.alipay',['order'=>$order->id])}}">支付宝支付</a>
                                    <a class="btn btn-sm btn-success" id="btn-wechat">微信支付</a>
                                    {{--分期付款按钮开始--}}
                                    {{--仅当订单金额大于分期最低金额时才展示按钮--}}
                                    @if($order->total_amount>=config('app.min_installment_amount'))
                                        <button class="btn btn-sm btn-info" id="btn-installment">分期付款</button>
                                    @endif
                                    {{--分期付款按钮结束--}}
                                </div>
                            @endif
                            {{--支付结束--}}
                            {{--如果订单的发货状态为已发货则展示确认收货按钮--}}
                            @if($order->ship_status === \App\Models\Order::SHIP_STATUS_DELIVERED)
                                <div class="receive-button">

                                    <button type="button" id="btn-receive" class="btn btn-sm btn-success">确认收货</button>

                                </div>
                            @endif
                            {{--不是众筹订单且订单已支付，并退款状态是未退款时，展示申请退款按钮--}}
                            @if($order->type!==\App\Models\Order::TYPE_CROWDFUNDING&&
                                    $order->paid_at&&
                                    $order->refund_status ===\App\Models\Order::REFUND_STATUS_PENDING)
                                <div class="refund-button">
                                    <button class="btn btn-sm btn-danger" id="btn-apply-refund">申请退款</button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 分期弹框开始 -->
    <div class="modal fade" id="installment-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">选择分期期数</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped text-center">
                        <thead>
                        <tr>
                            <th class="text-center">期数</th>
                            <th class="text-center">费率</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach(config('app.installment_fee_rate') as $count => $rate)
                            <tr>
                                <td>{{ $count }}期</td>
                                <td>{{ $rate }}%</td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-select-installment"
                                            data-count="{{ $count }}">选择
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                </div>
            </div>
        </div>
    </div>
    <!-- 分期弹框结束 -->
@endsection

@section('scriptsAfterJs')
    <script>
        $(document).ready(function () {

            //分期付款按钮点击事件
            $('#btn-installment').click(function () {
                //展示分期弹框
                $('#installment-modal').modal();
            });
            //选择分期期数按钮点击事件
            $('.btn-select-installment').click(function () {
                //调用创建分期付款接口
                axios.post('{{route('payment.installment',['order'=>$order->id])}}', {count: $(this).data('count')})
                    .then(function (response) {
                        //console.log(response.data);
                        //todo 跳转到分期付款页面
                        location.href = '/installments/' + response.data.id;
                    });
            });

            //微信支付按钮事件
            $('#btn-wechat').click(function () {
                swal({
                    //content参数可以是一个DOM元素，这里用JQuery动态生成一个img标签，并通过[0]的方式获取到DOM元素
                    content: $('<img src="{{route('payment.wechat',['order'=>$order->id])}}">')[0],
                    text: '敬请期待',
                    //buttons参数可以设置按钮显示文案
                    buttons: ['关闭', '以完成付款'],
                }).then(function (result) {
                    //如果用户点击了以完成支付按钮，则刷新页面
                    if (result) {
                        location.reload();
                    }
                });
            });

            //确认收货按钮
            $('#btn-receive').click(function () {
                //弹出确认框
                swal({
                    title: '确认已经收到商品？',
                    icon: 'warning',
                    //buttons: true,
                    dangerMode: true,
                    buttons: ['取消', '确认'],
                })
                    .then(function (ret) {
                        //点击取消按钮不做任何操作
                        if (!ret) {
                            return;
                        }
                        //ajax提交确认操作
                        axios.post('{{route('orders.received',[$order->id])}}')
                            .then(function () {
                                //刷新页面
                                location.reload();
                            });
                    });
            });

            //申请退款点击事件
            $('#btn-apply-refund').click(function () {
                swal({
                    text: '请输入退款理由',
                    content: 'input',
                    showCancelButton: true,
                    closeOnConfirm: false,

                    buttons: ['取消', '确定'],
                }).then(function (input) {
                    if (input === false) {
                        return;
                    }
                    //当用户点击swal弹出框上的按钮是触发这个函数
                    if (!input) {
                        swal('退款理由不可为空', '', 'error');
                        return;
                    }
                    //ajax请求退款接口
                    axios.post('{{route('orders.apply_refund',[$order->id])}}', {reason: input})
                        .then(function () {
                            swal('申请退款成功', '', 'success')
                                .then(function () {
                                    //用户点击确定按钮刷新页面
                                    location.reload();
                                });
                        });
                });
            });
        });
    </script>
@endsection