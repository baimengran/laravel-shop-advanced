<?php
/**
 *
 * @authors Your Name (you@example.org)
 * @date    2018-09-14 13:55:43
 * @version $Id$
 */

/**
 * 当前请求的路由名称转换为CSS
 * @return [type] [description]
 */
function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}


function ngrok_url($routeName, $parameters = [])
{
    //确认是开发环境并配置了NGROK_URL
    if (app()->environment('local') && $url = config('app.ngrok_url', 5)) {
        //route()函数第三个参数代表是否是绝对路径
        return $url . route($routeName, $parameters, false);
    }
    return route($routeName, $parameters);

}