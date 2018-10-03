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

        //支付宝支付
        Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');

        //支付宝前端回调测试
        Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');
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