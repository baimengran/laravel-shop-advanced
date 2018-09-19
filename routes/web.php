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


    });
    //商品详情页面
    Route::get('products/{product}', 'ProductsController@show')->name('products.show');
});

