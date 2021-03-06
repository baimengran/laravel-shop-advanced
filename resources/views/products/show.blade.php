@extends('layouts.app')
@section('title', $product->title)

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-body product-info">
                    <div class="row">
                        <div class="col-sm-5">
                            <img class="cover" src="{{ $product->image_url }}" alt="">
                        </div>
                        <div class="col-sm-7">
                            <div class="title">{{ $product->long_title?:$product->title }}</div>
                            @if($product->type===\App\Models\Product::TYPE_CROWDFUNDING)
                                {{--众筹商品模块开始--}}
                                <div class="crowdfunding-info">
                                    <div class="have-text">已筹到</div>
                                    <div class="total-amount">
                                        <span class="symbol">￥</span>
                                        {{$product->crowdfunding->total_amount}}
                                    </div>
                                    {{--使用bootstrap进度条组件--}}
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success progress-bar-striped"
                                             role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                             style="min-width: 1em;width: {{min($product->crowdfunding->percent,100)}}%">
                                        </div>
                                    </div>
                                    <div class="progress-info">
                                            <span class="current-progress">
                                                当前进度：{{$product->crowdfunding->percent}}%
                                            </span>
                                        <span class="pull-right user-count">
                                                {{$product->crowdfunding->user_count}}名支持者
                                        </span>
                                    </div>
                                    {{--如果众筹状态是众筹中，则输出提示语--}}
                                    @if($product->crowdfunding->status===\App\Models\CrowdfundingProduct::STATUS_FUNDING)
                                        <div>此项目必须在
                                            <span class="text-red">
                                                    {{$product->crowdfunding->end_at->format('Y-m-d H:i:s')}}
                                            </span>
                                            前得到
                                            <span class="text-red">
                                                    ￥{{$product->crowdfunding->target_amount}}
                                            </span>
                                            的支持才可成功，
                                            {{--Carbon对象的diffForHumans()方法可以计算出与当前时间的相对时间，--}}
                                            筹款将在
                                            <span class="text-red">
                                                    {{$product->crowdfunding->end_at->diffForHumans(now())}}
                                            </span>
                                            结束
                                        </div>
                                    @endif
                                </div>
                                {{--众筹商品模块结束--}}
                            @else
                                {{--普通商品模块开始--}}
                                <div class="price"><label>价格</label><em>￥</em><span>{{ $product->price }}</span></div>
                                <div class="sales_and_reviews">
                                    <div class="sold_count">累计销量 <span class="count">{{ $product->sold_count }}</span>
                                    </div>
                                    <div class="review_count">累计评价 <span
                                                class="count">{{ $product->review_count }}</span>
                                    </div>
                                    <div class="rating" title="评分 {{ $product->rating }}">评分
                                        <span class="count">
                                            {{ str_repeat('★', floor($product->rating)) }}{{ str_repeat('☆', 5 - floor($product->rating)) }}
                                        </span>
                                    </div>
                                </div>
                                {{--普通商品模块结束--}}
                            @endif

                            <div class="skus">
                                <label>选择</label>
                                <div class="btn-group" data-toggle="buttons">
                                    @foreach($product->skus as $sku)
                                        <label class="btn btn-default sku-btn" data-price="{{$sku->price}}"
                                               data-stock="{{$sku->stock}}" data-toggle="tooltip"
                                               title="{{ $sku->description }}" data-placement="bottom">
                                            <input type="radio" name="skus" autocomplete="off"
                                                   value="{{ $sku->id }}"> {{ $sku->title }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="cart_amount"><label>数量</label><input type="text" class="form-control input-sm"
                                                                             value="1"><span>件</span><span
                                        class="stock"></span></div>
                            <div class="buttons">
                                @if($favored)
                                    <button class="btn btn-success btn-disfavor">取消收藏</button>
                                @else
                                    <button class="btn btn-success btn-favor">❤ 收藏</button>
                                @endif

                                {{--普通商品与众筹商品收藏与下单--}}
                                @if($product->type===\App\Models\Product::TYPE_CROWDFUNDING)
                                    {{--众筹商品下单按钮开始--}}
                                    @if(Auth::check())
                                        @if($product->crowdfunding->status===\App\Models\CrowdfundingProduct::STATUS_FUNDING)
                                            <button class="btn btn-primary btn-crowdfunding">参与众筹</button>
                                        @else
                                            <button class="btn btn-primary disabled">
                                                {{\App\Models\CrowdfundingProduct::$statusMap[$product->crowdfunding->status]}}
                                            </button>
                                        @endif
                                    @else
                                        <a class="btn btn-primary" href="{{route('login')}}">请先登录</a>
                                    @endif
                                    {{--众筹商品下单按钮结束--}}
                                @else
                                    {{--普通商品加入购物车--}}
                                    <button class="btn btn-primary btn-add-to-cart">加入购物车</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="product-detail">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#product-detail-tab" aria-controls="product-detail-tab" role="tab"
                                   data-toggle="tab">商品详情</a>
                            </li>
                            <li role="presentation">
                                <a href="#product-reviews-tab" aria-controls="product-reviews-tab" role="tab"
                                   data-toggle="tab">用户评价</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="product-detail-tab">
                                {{--商品属性开始--}}
                                <div class="properties-list">
                                    <div class="properties-list-title">产品参数：</div>
                                    <ul class="properties-list-body">
                                        @foreach($product->grouped_properties as $name=>$value)
                                            <li>{{$name}}：{{join(' ',$value)}}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                {{--商品属性结束--}}
                                {{--商品描述开始--}}
                                <div class="product-description">
                                    {!! $product->description !!}
                                </div>
                                {{--商品描述结束--}}
                            </div>
                            <div role="tabpanel" class="tab-pane" id="product-reviews-tab">
                                {{--评论表开始--}}
                                <table class="table table-bordered table-striped">
                                    <thean>
                                        <tr>
                                            <td>用户</td>
                                            <td>商品</td>
                                            <td>评分</td>
                                            <td>评价</td>
                                            <td>时间</td>
                                        </tr>
                                    </thean>
                                    <tbody>
                                    @foreach($reviews as $review)
                                        <tr>
                                            <td>{{$review->order->user->name}}</td>
                                            <td>{{$review->productSku->title}}</td>
                                            <td>{{str_repeat('★',$review->rating)}}{{str_repeat('☆',5-$review->rating)}}</td>
                                            <td>{{$review->review}}</td>
                                            <td>{{$review->reviewed_at->format('y-m-d H:i')}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                {{--评论列表结束--}}
                            </div>
                        </div>
                    </div>

                    {{--猜你喜欢开始--}}
                    @if(count($similar)>0)
                        <div class="similar-products">
                            <div class="title">猜你喜欢</div>
                            <div class="row products-list">
                                @foreach($similar as $product)
                                    <div class="col-xs-3 product-item">
                                        <div class="product-content">
                                            <div class="top">
                                                <div class="img">
                                                    <a href="{{route('products.show',['product'=>$product->id])}}">
                                                        <img src="{{$product->image_url}}" alt=""/>
                                                    </a>
                                                </div>
                                                <div class="price"><b>￥</b>{{$product->price}}</div>
                                                <div class="title">
                                                    <a href="{{route('products.show',['product'=>$product->id])}}">
                                                        {{$product->title}}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    {{--猜你喜欢结束--}}

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scriptsAfterJs')
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip({trigger: 'hover'});
            $('.sku-btn').click(function () {
                $('.product-info .price span').text($(this).data('price'));
                $('.product-info .stock').text('库存:' + $(this).data('stock') + '件');
            });

            //收藏添加删除事件
            $('.btn-favor').click(function () {
                //发起一个post ajax请求，请求url通过后端route()函数生成
                axios.post('{{route('products.favor',['product'=>$product->id])}}')
                    .then(function () {//请求成功执行函数
                        swal('操作成功', '', 'success')
                            .then(function () {
                                //收藏成功后刷新页面展示收藏状态
                                location.reload();
                            });
                    }, function (error) {//请求失败执行函数
                        //如果返回码是401代表没有登录
                        if (error.response && error.response.status === 401) {
                            swal('请先登录', '', 'error');
                        } else if (error.response && error.response.data.msg) {
                            //其他有msg字段的情况，将msg提示给用户
                            swal(error.response.data.msg, '', 'error');
                        } else {
                            //其他无msg情况，系统出错
                            swal('系统出错', '', 'error');
                        }
                    });
            });

            //取消收藏
            $('.btn-disfavor').click(function () {
                axios.delete('{{route('products.disfavor',['product'=>$product->id])}}')
                    .then(function () {
                        swal('操作成功', '', 'success')
                            .then(function () {
                                location.reload();
                            })
                    })
            });

            //加入购物车按钮点击事件
            $('.btn-add-to-cart').click(function () {
                //请求加入购物车接口
                // var sku_id = $('label.active input[name=skus]').val();
                // alert(sku_id);
                axios.post('{{route('cart.add')}}', {
                    sku_id: $('label.active input[name=skus]').val(),
                    amount: $('.cart_amount input').val(),
                })
                    .then(function () {//请求成功回调函数
                        //商品加入购物车后跳转
                        swal({
                            title: "加入购物车成功",
                            icon: "success",
                            buttons: ['取消', '进入购物车'],
                            dangerMode: true,
                        }).then(function (willGo) {
                            if (!willGo) {
                                return;
                            }
                            location.href = '{{route('cart.index')}}';
                        });
                    }, function (error) {//请求失败回调函数
                        if (error.response.status === 401) {
                            //http状态码为401代表用户未登录
                            swal('请先登录', '', 'error');
                        } else if (error.response.status === 422) {
                            //Laravel 里输入参数校验不通过抛出的异常所对应的 Http 状态码是 422，
                            // 具体错误信息会放在返回结果的 errors 数组里，所以这里我们通过 error.response.data.errors
                            // 来拿到所有的错误信息。最后把所有的错误信息拼接成 Html 代码并弹框告知用户。
                            //http状态吗为422代表用户输入校验失败
                            var html = '<div>';
                            //_.each 是 lodash 这个前端库提供的方法，类似 php 里面的 foreach
                            _.each(error.response.data.errors, function (errors) {
                                _.each(errors, function (error) {
                                    html += error + '<br>';
                                })
                            });
                            html += '</div>';
                            swal({
                                content: $(html)[0],
                                icon: 'error'
                            });
                        } else {
                            //其他情况系统故障
                            swal('系统错误', '', 'error');
                        }
                    });

            });

            //参与众筹 按钮点击事件
            $('.btn-crowdfunding').click(function () {
                //判断是否选中SKU
                if (!$('label.active input[name=skus]').val()) {
                    swal('请先选择商品');
                    return;
                }

                //把用户的地址以JSON的形式放入页面，赋值给addresses变量
                var addresses ={!! json_encode(Auth::check()?Auth::user()->addresses:[]) !!}
                //使用JQuery动态创建一个表单
                var $form = $('<form class="form-horizontal" role="form"></form>');
                //表单中添加一个收货地址的下拉框
                $form.append(
                    '<div class="form-group">' +
                    '<label class="control-label col-sm-3">选择地址</label>' +
                    '<div class="col-sm-9">' +
                    '<select class="form-control" name="address_id"></select>' +
                    '</div></div>');
                //循环每一个收货地址
                addresses.forEach(function (address) {
                    //把收货地址添加到收获地址下拉框选项中
                    $form.find('select[name=address_id]')
                        .append("<option value='" + address.id + "'>" +
                            address.full_address + '' + address.contact_name + '' + address.contact_phone +
                            '</option>');
                });
                //在表单中添加一个名为 购买数量 的输入框
                $form.append('<div class="form-group">' +
                    '<label class="control-label col-sm-3">购买数量</label>' +
                    '<div class="col-sm-9">' +
                    '<input class="form-control" name="amount">' +
                    '</div></div>');

                //调用SweetAlert弹框
                swal({
                    text: '参与众筹',
                    content: $form[0],//弹框的内容是上面穿件的form表单
                    buttons: ['取消', '确定'],
                }).then(function (ret) {
                    //如果用户没有点确定按钮，则什么也不做
                    if (!ret) {
                        return;
                    }
                    //构建请求参数
                    var req = {
                        address_id: $form.find('select[name=address_id]').val(),
                        amount: $form.find('input[name=amount]').val(),
                        sku_id: $('label.active input[name=skus]').val(),
                    };
                    //调用众筹商品下单接口
                    axios.post('{{route('crowdfunding_orders.store')}}', req)
                        .then(function (response) {
                            //订单创建成功，跳转到订单详情页
                            swal('订单提交成功', '', 'success')
                                .then(() => {
                                    location.href = '/orders/' + response.data.id;
                                });
                        }, function (error) {
                            //输入参数校验失败，展示失败原因
                            if (error.response.status === 422) {
                                var html = '<div>';
                                _.each(error.response.data.errors, function (errors) {
                                    _.each(errors, function (error) {
                                        html += error + '<br>';
                                    });
                                });
                                html += '</div>';
                                swal({
                                    content: $(html)[0],
                                    icon: 'error',
                                });
                            } else if (error.response.status === 403) {
                                swal(error.response.data.msg, '', 'error');
                            } else {
                                swal('系统错误', '', 'error');
                            }
                        });
                });
            });

        });
    </script>
@endsection