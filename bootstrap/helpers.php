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
function route_class(){
	return str_replace('.','-',Route::currentRouteName());
}