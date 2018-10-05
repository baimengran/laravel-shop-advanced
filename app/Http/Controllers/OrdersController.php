<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    /**
     * 添加订单
     * @param OrderRequest $request 请求信息
     * @param OrderService $orderService 订单添加函数
     * @return mixed OrderService封装函数处理结果
     */
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user = $request->user();
        $address = UserAddress::find($request->input('address_id'));

        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'));
    }

    /**
     * 订单列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $orders = Order::query()
            //使用with预加载避免N+1
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view('orders.index', ['orders' => $orders]);

    }

    /**
     * 显示订单详情
     * @param Order $order
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Order $order, Request $request)
    {
        //授权
        $this->authorize('view', $order);
        //这里的 load() 方法与 with() 预加载方法有些类似，称为 延迟预加载，
        //不同点在于 load() 是在已经查询出来的模型上调用，而 with() 则是在 ORM 查询构造器上调用
        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }


    public function received(Order $order, Request $request)
    {
        //校验
        $this->authorize('view', $order);
        //判断订单发货状态是否以发货
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('发货状态不正确');
        }

        //更新发货状态为已收到
        $order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);

        //返回上一页
        return $order;
    }
}
