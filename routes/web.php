<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
use App\Models\Order;
use Illuminate\Support\Facades\Route;

//Route::get('/', 'PagesController@root')->name('root');
//进入网站跳转到商品列表页面
Route::redirect('/', '/products')->name('root');
//商品列表页面
Route::get('products', 'ProductsController@index')->name('products.index');


Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    //显示邮件验证页面
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');
    //验证邮箱处理方法
    Route::get('/email_verification/verify', 'EmailVerificationController@verify')->name('email_verification.verify');
    //手动发送验证邮件
    Route::get('email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');

    //注册用户邮箱验证中间件
    Route::group(['middleware' => 'email_verified'], function () {
        //收货地址列表
        Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
        //收货地址添加显示
        Route::get('user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
        //收获地址添加动作
        Route::post('user_addresses', 'UserAddressesController@store')->name('user_addresses.store');
        //收获地址修改显示
        Route::get('user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
        //收获地址修改动作
        Route::put('user_address/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
        //删除收货地址
        Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');

        //商品收藏添加
        Route::post('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
        //商品收藏删除
        Route::delete('products/{product}/favorite', 'productsController@disfavor')->name('products.disfavor');
        //商品收藏列表
        Route::get('products/favorites', 'productsController@favorites')->name('products.favorites');

        //添加购物车
        Route::post('cart', 'CartController@add')->name('cart.add');
        //购物车列表
        Route::get('cart', 'CartController@index')->name('cart.index');
        //购物车移除商品
        Route::delete('cart/{sku}', 'CartController@remove')->name('cart.remove');

        //提交订单
        Route::post('orders', 'OrdersController@store')->name('orders.store');
        //订单列表
        Route::get('orders', 'OrdersController@index')->name('orders.index');
        //订单详情
        Route::get('orders/{order}', 'OrdersController@show')->name('orders.show');
        //订单收货
        Route::post('orders/{order}/received', 'OrdersController@received')->name('orders.received');
        //支付宝支付
        Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
        //支付宝前端回调测试
        Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');

        //商品评价页面
        Route::get('orders/{order}/review', 'OrdersController@review')->name('orders.review.show');
        //商品评价提交
        Route::post('orders/{order}/review', 'OrdersController@sendReview')->name('orders.review.store');

        //申请退款
        Route::post('orders/{order}/apply_refund', 'OrdersController@applyRefund')->name('orders.apply_refund');

    });

    //支付宝沙箱支付测试
//    Route::get('alipay',function(){
//        return app('alipay')->web([
//            'out_trade_no'=>time(),
//            'total_amount'=>'1',
//            'subject'=>'test subject -测试'
//        ]);
//    });

});
//商品详情页面
Route::get('products/{product}', 'ProductsController@show')->name('products.show');

//支付宝服务器回调
//服务器端回调的路由不能放到带有 auth 中间件的路由组中，因为支付宝的服务器请求不会带有认证信息
Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');

//测试sql语句
Route::get('aaa', function () {
    $order = Order::find(15);
    $order->load('items.product');
    //循环遍历订单的销量
    foreach ($order->items as $item) {
        $product = $item->product;
        //计算对应商品的销量
        $soldCount = App\Models\OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($query) {
                $query->whereNotNull('paid_at');//关联的订单已支付
            })->sum('amount');
    }
    return $soldCount;
}
)->name('a');

Route::get('li', function () {
    $event = App\Models\Order::find(20);
    $items = $event->items()->with(['product'])->ge();

    foreach ($items as $item) {
        //商品id与当前订单商品id相同
        $result = App\Models\OrderItem::where('product_id', $item->product_id)
            //订单是已经支付的
            ->whereHas('order', function ($query) {
                $query->whereNotNull('paid_at');
            })->first([
                //first() 方法接受一个数组作为参数，代表此次 SQL 要查询出来的字段，
                //默认情况下 Laravel 会给数组里面的值的两边加上 ` 这个符号，比如 first(['name', 'email']) 生成的 SQL
                // 会类似：select `name`, `email` from xxx
                //  如果直接传入 first(['count(*) as review_count', 'avg(rating) as rating'])，
                //最后生成的 SQL 肯定是不正确的。这里用 DB::raw() 方法来解决这个问题，
                //Laravel 在构建 SQL 的时候如果遇到 DB::raw() 就会把 DB::raw() 的参数原样拼接到 SQL 里
                DB::raw('count(*) as review_count'),
                DB::raw('avg(rating) as rating')
            ]);
        $item->product->update([
            'rating' => $result->rating,
            'review_count' => $result->review_count,
        ]);
    }
    return 'd';
});

