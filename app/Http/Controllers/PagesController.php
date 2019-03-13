<?php
/**
 * 自定义页面控制器
 *
 *
 * @author bai (13466320356@163.com)
 * @version 1.0
 * @package App.Http.Controllers
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * 自定义页面控制器类
 *
 * 此控制器处理所有自定义页面的逻辑
 * @package App\Http\Controllers
 */
class PagesController extends Controller
{
    /**
     * 首页展示
     *
     * 调用View展示首页模板
     * @return \Illuminate\View\View|null 首页模板
     */
    public function root()
    {
        return view('pages.root');
    }

    /**
     * 邮箱验证页面展示
     *
     * 调用View展示邮箱验证页面
     * @param Request $request 请求体
     * @return \Illuminate\View\View|null 邮箱验证模板
     */
    public function emailVerifyNotice(Request $request)
    {
        return view('pages.email_verify_notice');
    }
}
