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
                            <div class="title">{{ $product->title }}</div>
                            <div class="price"><label>价格</label><em>￥</em><span>{{ $product->price }}</span></div>
                            <div class="sales_and_reviews">
                                <div class="sold_count">累计销量 <span class="count">{{ $product->sold_count }}</span></div>
                                <div class="review_count">累计评价 <span class="count">{{ $product->review_count }}</span>
                                </div>
                                <div class="rating" title="评分 {{ $product->rating }}">评分 <span
                                            class="count">{{ str_repeat('★', floor($product->rating)) }}{{ str_repeat('☆', 5 - floor($product->rating)) }}</span>
                                </div>
                            </div>
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
                                <button class="btn btn-primary btn-add-to-cart">加入购物车</button>
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
                                {!! $product->description !!}
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
                            if(!willGo){
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


        });
    </script>
@endsection