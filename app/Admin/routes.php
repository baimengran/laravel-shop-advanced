<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix' => config('admin.route.prefix'),
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    //用户列表展示
    $router->get('users', 'UsersController@index');
    //商品列表展示
    $router->get('products', 'ProductsController@index');
    //商品添加页面展示
    $router->get('products/create', 'ProductsController@create');
    //商品添加动作
    $router->post('products', 'ProductsController@store');
    //商品修改页面展示
    $router->get('products/{id}/edit', 'ProductsController@edit');
    //商品修改动作
    $router->put('products/{id}', 'ProductsController@update');
    //订单列表
    $router->get('orders', 'OrdersController@index')->name('admin.orders.index');
    //订单详情
    $router->get('orders/{order}', 'OrdersController@show')->name('admin.orders.show');
    //订单物流
    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship');
    //退款处理
    $router->post('orders/{order}/refund', 'OrdersController@handleRefund')->name('admin.orders.handle_refund');
    //优惠卷列表
    $router->get('coupon_codes', 'CouponCodesController@index');
    //优惠卷新增动作
    $router->post('coupon_codes', 'CouponCodesController@store');
    //优惠卷新增
    $router->get('coupon_codes/create', 'CouponCodesController@create');
    //编辑优惠卷
    $router->get('coupon_codes/{id}/edit', 'CouponCodesController@edit');
    //编辑优惠卷动作
    $router->put('coupon_codes/{id}', 'CouponCodesController@update');
    //删除优惠卷
    $router->delete('coupon_codes/{id}', 'CouponCodesController@destroy');

});
