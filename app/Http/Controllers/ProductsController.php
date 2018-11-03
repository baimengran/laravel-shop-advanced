<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductsController extends Controller
{
    /**
     * 商品列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {

        $page = $request->input('page', 1);
        $perPage = 16;
        //构建查询
        $params = [
            'index' => 'products',
            'type' => '_doc',
            'body' => [
                'from' => ($page - 1) * $perPage,//通过当前页数与每页数量计算偏移量
                'size' => $perPage,
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['term' => ['on_sale' => true]],
                        ],
                    ],
                ],
            ],
        ];


//        //创建查询构建器
//        $builder = Product::query()->where('on_sale', true);
//        //判断是否有提交search参数，如果有就赋值给$search变量
//        //search餐宿用来模糊搜索商品
//        if ($search = $request->input('search', '')) {
//            $like = '%' . $search . '%';
//            //模糊搜索商品标题、商品详情、SKU标题、SKU描述
//            $builder->where(function ($query) use ($like) {
//                $query->where('title', 'like', $like)
//                    ->orWhere('description', 'like', $like)
//                    ->orWhereHas('skus', function ($query) use ($like) {
//                        $query->where('title', 'like', $like)
//                            ->orWhere('description', 'like', $like);
//                    });
//            });
//        }

//        //如果有传入category_id字段，并在数据库中有对应的类目
//        if ($request->input('category_id') && $category = Category::query()->find($request->input('category_id'))) {
//            //如果这是一个父类目
//            if ($category->is_directory) {
//                //则筛选出该父类目下的所有子类目商品
//                $builder->whereHas('category', function ($query) use ($category) {
//                    $query->where('path', 'like', $category->path . $category->id . '-%');
//                });
//            } else {
//                //如果这不是一个类目，则直接筛选此类目下的商品
//                $builder->where('category_id', $category->id);
//            }
//        }

        //是否提交order参数，如果有就赋值给$order变量
        //order参数会用来控制商品排序规则
        if ($order = $request->input('order', '')) {
            //是否以_asc或_desc结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                //如果字符串的开头是这三个字符串之一，说明是合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    //根据传入的排序值来构造排序参数
                    $params['body']['sort'] = [[$m[1] => $m[2]]];
                }
            }
        }

        //按类目搜索
        if ($request->input('category_id') && $category = Category::query()->find($request->input('category_id'))) {
            if ($category->is_directory) {
                //如果是一个父类目，则使用category_path来筛选
                $params['body']['query']['bool']['filter'][] = [
                    'prefix' => ['category_path' => $category->path . $category->id . '-'],
                ];
            } else {
                //否则直接通过category_id筛选
                $params['body']['query']['bool']['filter'][] = [
                    'term' => ['category_id' => $category->id],
                ];
            }
        }

        //关键字搜索
        if ($search = $request->input('search', '')) {
            //将搜索词根据空格拆分成数组，并过滤掉空项
            $keywords = array_filter(explode(' ', $search));
            $params['body']['query']['bool']['must'] = [];
            //遍历搜索词数组，分别添加到must查询中
            foreach ($keywords as $keyword) {
                $params['body']['query']['bool']['must'][] = [
                    'multi_match' => [
                        'query' => $keyword,
                        'fields' => [
                            'title^2',
                            'long_title^2',
                            'category^2',//类目名称
                            'description',
                            'skus_title',
                            'skus_description',
                            'properties_value',//标签
                        ],
                    ],
                ];
            }
        }

        //用户搜索或类目筛选时使用elasticsearch聚合
        if ($search || isset($category)) {
            $params['body']['aggs'] = [
                'properties' => [
                    'nested' => [
                        'path' => 'properties',
                    ],
                    'aggs' => [
                        'properties' => [
                            'terms' => [
                                'field' => 'properties.name',
                            ],
                            'aggs' => [
                                'value' => [
                                    'terms' => [
                                        'field' => 'properties.value',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        $propertyFilters = [];
        //从用户请求参数中获取filters
        if ($filterString = $request->input('filters')) {
            //将获取到的字符串用符号 | 拆分成数组
            $filterArray = explode('|', $filterString);
            foreach ($filterArray as $filter) {
                //将字符串用符号 ： 拆分成两部分并分别赋值给$name 和$value 两个变量
                list($name, $value) = explode(':', $filter);
                //将筛选的属性添加到数组中
                $propertyFilters[$name] = $value;
                //添加到filter类型中
                $params['body']['query']['bool']['filter'][] = [
                    //筛选nested类型下的属性，
                    'nested' => [
                        //指明nested字段
                        'path' => 'properties',
                        'query' => [
                            ['term' => ['properties.search_value' => $filter]],
                        ],
                    ],
                ];
            }
        }

        //服务提供者创建elasticsearch查询对象
        $result = app('es')->search($params);

        $properties = [];
        //如果返回结果里有aggregations字段，说明做了分面搜索
        if (isset($result['aggregations'])) {
            //使用collect函数将返回值转为集合
            $properties = collect($result['aggregations']['properties']['properties']['buckets'])
                ->map(function ($bucket) {
                    //通过map方法取出需要的字段
                    return [
                        'key' => $bucket['key'],
                        'values' => collect($bucket['value']['buckets'])->pluck('key')->all(),
                    ];
                })
                ->filter(function ($property) use ($propertyFilters) {
                    //过滤掉只剩一个值或已经在筛选条件里的属性
                    return count($property['values']) > 1 && !isset($propertyFilters['key']);
                });
        }

        //通过collect函数将返回的结果转为集合，并通过集合的pluck方法取到返回的商品ID数组
        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();
        //通过whereIn方法从数据库中读取商品数据
        $products = Product::query()
            ->whereIn('id', $productIds)
            //使用Mysql的FIND_IN_SET方法
            //orderByRaw可以使用原生的SQL查询
            ->orderByRaw(sprintf("FIND_IN_SET(id,'%s')", join(',', $productIds)))
            ->get();
        //返回一个LengthAwarePaginator对象
        $pager = new LengthAwarePaginator($products, $result['hits']['total'], $perPage, $page, [
            'path' => route('products.index', false),//手动构建分页url
        ]);

        return view('products.index', [
            'products' => $pager,
            'filters' => [
                'search' => $search,
                'order' => $order
            ],
            'category' => $category ?? null,
            'properties' => $properties,
            'propertyFilters' => $propertyFilters,
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
