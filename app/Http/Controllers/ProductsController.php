<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * 商品列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        //创建查询构建器
        $builder = Product::query()->where('on_sale', true);
        //判断是否有提交search参数，如果有就赋值给$search变量
        //search餐宿用来模糊搜索商品
        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            //模糊搜索商品标题、商品详情、SKU标题、SKU描述
            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        //如果有传入category_id字段，并在数据库中有对应的类目
        if ($request->input('category_id') && $category = Category::query()->find($request->input('category_id'))) {
            //如果这是一个父类目
            if ($category->is_directory) {
                //则筛选出该父类目下的所有子类目商品
                $builder->whereHas('category', function ($query) use ($category) {
                    $query->where('path', 'like', $category->path . $category->id . '-%');
                });
            } else {
                //如果这不是一个类目，则直接筛选此类目下的商品
                $builder->where('category_id', $category->id);
            }
        }

        //是否提交order参数，如果有就赋值给$order变量
        //order参数会用来控制商品排序规则
        if ($order = $request->input('order', '')) {
            //是否以_asc或_desc结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                //如果字符串的开头是这三个字符串之一，说明是合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    //滚局传入的排序值来构造排序参数
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }
        $products = $builder->paginate(16);

        return view('products.index', [
            'products' => $products,
            'filters' => ['search' => $search, 'order' => $order],
            'category' => $category ?? null,
        ]);
    }

    /**
     * 商品详情
     * @param Product $product
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws InvalidRequestException
     */
    public function show(Product $product, Request $request)
    {
        //判断商品是否已经上架，如果没上架抛出异常
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品未上架');
        }
        //用户未登录返回空
        $favored = false;
        //用户以登录返回对应用户对象
        if ($user = $request->user()) {
            //从当前用户已经收藏的商品中搜索id为当前商品的id
            //boolval()函数用于把值转换为布尔值
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }

        $reviews = OrderItem::with(['order.user', 'productSku'])//预加载关联关系
        ->where('product_id', $product->id)//筛选商品id
        ->whereNotNull('reviewed_at')//筛选以评价的
        ->orderBy('reviewed_at', 'desc')//按评价时间倒序
        ->limit(10)//取出10条
        ->get();
        return view('products.show', [
            'product' => $product,
            'favored' => $favored,
            'reviews' => $reviews,
        ]);
    }

    /**
     * 收藏添加
     * @param Product $product
     * @param Request $request
     * @return array
     */
    public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }
        $user->favoriteProducts()->attach($product);
        return [];
    }

    /**
     * 删除收藏
     * @param Product $product
     * @param Request $request
     * @return array
     */
    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product);
        return [];
    }

    /**
     * 收藏列表展示
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function favorites(Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate(16);
        return view('products.favorites', ['products' => $products]);
    }
}
