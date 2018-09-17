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
});
