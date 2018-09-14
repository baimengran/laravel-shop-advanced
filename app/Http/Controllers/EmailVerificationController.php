<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Cache;

class EmailVerificationController extends Controller
{
    /**
     * 邮箱验证
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws Exception
     */
    public function verify(Request $request)
    {
        //从rul中获取'email'和‘token’两个参数
        $email = $request->input('email');
        $token = $request->input('token');
        //如果有一个为空说明不是一个合法的验证连接，直接抛出异常
        if (!$email || !$token) {
            throw new Exception('验证连接不正确');
        }
        //从缓存中读取数据，我们把从url中获取的‘token’与缓存中的值做对比
        //如果缓存中不存在或者返回的值与url中的‘token’不一致就抛出异常
        if ($token != Cache::get('email_verification_' . $email)) {
            throw new Exception('验证连接不正确或已经过期');
        }

        //根据邮箱从数据库中获取对应的用户
        //通常来说能通过token校验的情况下不可能出现用户不存在
        //但是为了代码的健壮性我们还是做个判断
        if (!$user = User::where('email', $email)->first()) {
            throw new Exception('用户不存在');
        }
        //将制定的key从缓存中删除，由于已经完成了验证，这个缓存就没有必要了
        Cache::forget('email_verification' . $email);
        //最关键的，把对应用户的‘email_verified’字段改为true
        $user->update(['email_verified' => true]);

        //最后告知用户邮箱验证成功
        return view('pages.success', ['msg' => '邮箱验证成功']);
    }

    public function send(Request $request)
    {
        $user = $request->user();
        //判断用户是否已经激活
        if ($user->email_verified) {
            throw new Exception('您已经验证过邮箱了');
        }

        //调用notify()方法用来发送我们定义好的通知类
        $user->notify(new EmailVerificationNotification());
        return view('pages.success', ['msg' => '邮件发送成功']);
    }

}
