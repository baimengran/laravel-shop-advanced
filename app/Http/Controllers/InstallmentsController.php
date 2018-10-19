<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;

class InstallmentsController extends Controller
{
    //
    /**
     * 分期付款列表页
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $installments = Installment::query()
            ->where('user_id', $request->user()->id)
            ->paginate(10);

        return view('installments.index', ['installments' => $installments]);
    }


    public function show(Installment $installment)
    {
        //取出当前分期付款的所有还款计划，并按还款顺序排列
        $item = $installment->items()->orderBy('sequence')->get();

        return view('installments.show', [
            'installment' => $installment,
            'items' => $item,
            //下一个未完成还款的还款计划
            'nextItem' => $item->where('paid_at', null)->first(),
        ]);
    }
}
