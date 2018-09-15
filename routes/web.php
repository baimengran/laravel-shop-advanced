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

Route::get('/', 'PagesController@root')->name('root');
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
    });

});

