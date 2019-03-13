<?php
/**
 * 辅助函数文件
 *
 * 此辅助函数文件包含各类辅助函数，以提供程序扩展功能
 *
 * @authors bai (13466320356@163.com)
 * @date    2018-09-14 13:55:43
 * @version 1.0
 * @package bootstrap
 */

use Moontoast\Math\BigNumber;

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

/**
 * 调用BigNumber扩展设置数字精度
 * @param $number
 * @param int $scale
 * @return BigNumber
 */
function big_number($number, $scale = 2)
{
    return new BigNumber($number, $scale);
}