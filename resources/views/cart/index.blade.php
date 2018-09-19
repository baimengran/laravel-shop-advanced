
{{--/**--}}
 {{--* Created by PhpStorm.--}}
 {{--* User: Administrator--}}
 {{--* Date: 2018/9/19--}}
 {{--* Time: 23:12--}}
 {{--*/--}}
@extends('layouts.app')

@section('title', '购物车')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">我的购物车</div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>商品信息</th>
                            <th>单价</th>
                            <th>数量</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody class="product_list">
                        @foreach($cartItems as $item)
                            <tr data-id="{{ $item->productSku->id }}">
                                <td>
                                    <input type="checkbox" name="select"
                                           value="{{ $item->productSku->id }}" {{ $item->productSku->product->on_sale ? 'checked' : 'disabled' }}>
                                </td>
                                <td class="product_info">
                                    <div class="preview">
                                        <a target="_blank" href="{{ route('products.show', [$item->productSku->product_id]) }}">
                                            <img src="{{ $item->productSku->product->image_url }}">
                                        </a>
                                    </div>
                                    <div @if(!$item->productSku->product->on_sale) class="not_on_sale" @endif>
                                        <span class="product_title">
                                            <a target="_blank" href="{{ route('products.show', [$item->productSku->product_id]) }}">{{ $item->productSku->product->title }}</a>
                                        </span>
                                        <span class="sku_title">{{ $item->productSku->title }}</span>
                                        @if(!$item->productSku->product->on_sale)
                                            <span class="warning">该商品已下架</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="price">￥{{ $item->productSku->price }}</span></td>
                                <td>
                                    <input type="text" class="form-control input-sm amount"
                                           @if(!$item->productSku->product->on_sale) disabled @endif name="amount"
                                           value="{{ $item->amount }}">
                                </td>
                                <td>
                                    <button class="btn btn-xs btn-danger btn-remove">移除</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scriptsAfterJs')
<script>
    $(document).ready(function(){
        //监听移除按钮事件
        $('.btn-remove').click(function(){
            //alert(1);
            //$(this)获取当前点击移除按钮的jquery对象
            //closest()方法获取匹配选择器的第一个祖先元素（当前点击的移除按钮之上的<tr>标签）
            //data('id')方法获取设置的data-id属性值（对应的SKU的id）
            var id = $(this).closest('tr').data('id');
            swal({
                title:"确认移除该商品？",
                icon:"warning",
                buttons:['取消','确定'],
                dangerMode:true,
            }).then(function(willDelete){
                //用户点击确定按钮，willDelete值=true，否则=false
                if(!willDelete){
                    return;
                }
                axios.delete('/cart/'+id).then(function(){
                    location.reload();
                })
            })
        });

        //监听全选/取消全选单选框变更事件
        $('#select-all').change(function(){
            //获取单选框选中状态
            //prop()方法可以知道标签中是否包含某个属性，当单选框被勾选时，对应的标签会新增一个checked属性
            var checked = $(this).prop('checked');
            //获取所有name=select并且不带有disabled属性的单选框
            //对于已经下架的商品，不能被勾选，因此需要加上:not([disabled])条件
            $('input[name=select][type=checkbox]:not([disabled])').each(function(){
                //循环将勾选状态设置为与目标单选框一致
                $(this).prop('checked',checked);
            })
        });

    });
</script>
    @endsection