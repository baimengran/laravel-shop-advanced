<?php

namespace App\Listeners;

use App\Notifications\EmailVerificationNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

//implements ShouldQueue 让这个监听器异步执行
class RegisteredListener implements ShouldQueue
{
    /**
     * The events handled by the listener.
     *
     * @var array
     */
    public static $listensFor = [
        //
    ];

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 注册后发送验证邮件.
     *
     * @param  object $event
     * @return void
     */
    public function handle(Registered $event)
    {
        //获取刚刚注册的用户
        $user = $event->user;
        //调用notify发送通知
        //$user->notify(new EmailVerificationNotification());
        Mail::to($user->notify(new EmailVerificationNotification()));
    }
}
