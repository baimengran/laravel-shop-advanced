<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\UserAddressRequest;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class UserAddressesController extends Controller
{
    //

    public function index(Request $request)
    {
        return view('user_addresses.index', ['addresses' => $request->user()->addresses]);
    }


    public function create()
    {
        return view('user_addresses.create_and_edit', ['address' => new UserAddress()]);
    }

    public function store(UserAddressRequest $request)
    {
        //获取当前登录用户与地址的关联关系，并创建一个新纪录（create方法）
        //$request->only()通过白名单的方式从用户提交的数据里获取所需的数据
        $request->user()->addresses()->create($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]));
        return redirect()->route('user_addresses.index');
    }


    public function edit(UserAddress $user_address)
    {
        $this->authorize('update', $user_address);
        return view('user_Addresses.create_and_edit', ['address' => $user_address]);
    }


    public function update(UserAddress $user_address, UserAddressRequest $request)
    {
        $this->authorize('update', $user_address);
        $user_address->update($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]));
        return redirect()->route('user_addresses.index');
    }

    public function destroy(UserAddress $user_address)
    {
        $this->authorize('delete', $user_address);
        try {
            $user_address->delete();
            return [];
        } catch (\Exception $e) {
            throw new InvalidRequestException('删除失败');
        }
    }
}
