<?php
/**
 *  优惠校验卷控制器
 *
 * @author Bai
 * @version 1.0
 * @category App.Http
 * @package App.Http.Controllers
 */
namespace App\Http\Controllers;

use App\Exceptions\CouponCodeUnavailableException;
use App\Models\CouponCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 *
 * 优惠校验卷控制器
 *
 * 这个控制器主要负责检验优惠卷是否存在和正常使用功能
 * @package App\Http\Controllers
 */
class CouponCodesController extends Controller
{
    /**
     * 校验优惠卷
     *
     * 通过$code和$request参数传入优惠卷码和请求对象
     * 首先通过优惠卷码验证优惠卷是否存在，如果不存在调用CouponCodeUnavailableException异常处理类抛出优惠卷不存在信息
     * 优惠卷存在则调用CouponCode模型中checkAvailable()方法验证优惠卷其他信息，详细信息见CouponCode模型中checkAvailable()方法
     * @param string $code 优惠码
     * @param Request $request 请求对象
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null 优惠卷CouponCode模型
     * @throws CouponCodeUnavailableException 优惠卷异常处理类
     */
    public function show($code, Request $request)
    {
        //$record = CouponCode::query()->where('code', $code)->first();
        //判断优惠卷是否存在
        if (!$record = CouponCode::query()->where('code', $code)->first()) {
            //abort() 方法可以直接中断程序的运行，接受的参数会变成 Http 状态码返回。
            //abort(404);
            throw new CouponCodeUnavailableException('优惠卷不存在');
        }
//        //如果优惠卷没有启用，则等同于不存在
//        if (!$record->enabled) {
//            abort(404);
//        }
//
//        if ($record->total - $record->used <= 0) {
//            return response()->json(['msg' => '该优惠卷已被兑完'], 403);
//        }
//        if ($record->not_before && $record->not_before->gt(Carbon::now())) {
//            return response()->json(['msg' => '该优惠卷现在还不能使用'], 403);
//        }
//        if ($record->not_after && $record->not_after->lt(Carbon::now())) {
//            return response()->json(['msg' => '该优惠卷已过期'], 403);
//        }checkAvailable

        $record->checkAvailable($request->user());
        return $record;
    }
}
