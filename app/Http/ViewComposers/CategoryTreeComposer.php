<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16
 * Time: 18:18
 */

namespace App\Http\ViewComposers;


use App\Services\CategoryService;
use Illuminate\View\View;

class CategoryTreeComposer
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * 渲染指定模板时，laravel会调用compose方法
     * @param View $view
     */
    public function compose(View $view)
    {
        //使用with方法注入变量
        $view->with('categoryTree', $this->categoryService->getCategoryTree());
    }

}