<?php
/**
 * 购物车控制器
 *
 * @author bai (13466320356@163.com)
 * @version 1.0
 * @package App.Http.Controllers
 */
namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use App\Services\CartService;
use Illuminate\Http\Request;

/**
 * 购物车控制器
 *
 * 购物车相关功能控制器，包括购物车列表、添加和删除
 * @package App\Http\Controllers
 */
class CartController extends Controller
{
    /**
     *
     * @var CartService
     */
    protected $cartService;

    //利用laravel自动解析功能注入CartService
    public function __construct(CartService $cartService)
    {
        $this->cartService=$cartService;
    }

    //
    public function add(AddCartRequest $request)
    {
        //获取添加购物车商品SKU的id
        $skuId = $request->input('sku_id');
        //获取添加购物车商品的数量
        $amount = $request->input('amount');

        $this->cartService->add($skuId,$amount);

        return [];
    }

    /**
     * 购物车列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->orderBy('last_used_at','desc')->get();
        $cartItems = $this->cartService->get();

        return view('cart.index', ['cartItems' => $cartItems,'addresses'=>$addresses]);
    }


    public function remove(ProductSku $sku, Request $request)
    {
        $this->cartService->remove($sku->id);
        return [];
    }
}
