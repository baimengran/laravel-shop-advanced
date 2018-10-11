<?php

namespace App\Http\Controllers;

use App\Exceptions\CouponCodeUnavailableException;
use App\Models\CouponCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CouponCodesController extends Controller
{
    //
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
