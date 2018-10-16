<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16
 * Time: 17:20
 */

namespace App\Services;


use App\Models\Category;

class CategoryService
{

    /**
     * 递归方法，返回所有类目
     * @param null $parentId 要获取子类目的父类目ID，null代表获取所有类目
     * @param null $allCategories 数据库中所有类目，null代表需呀从数据库中查询
     * @return Category[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getCategoryTree($parentId = null, $allCategories = null)
    {
        if (is_null($allCategories)) {
            //从数据库中一次性取出所有类目
            $allCategories = Category::all();
        }

        return $allCategories->where('parent_id', $parentId)//从所有类目中筛选出父类目ID为$parentId的类目
        //遍历这些类目，并用返回值构建一个新的集合
        ->map(function (Category $category) use ($allCategories) {
            $data = ['id' => $category->id, 'name' => $category->name];
            //如果当前类目不是父类目，则直接返回
            if (!$category->is_directory) {
                return $data;
            }
            //否则递归调用本方法，将返回值放入children字段中
            $data['children'] = $this->getCategoryTree($category->id, $allCategories);
            return $data;
        });
    }
}