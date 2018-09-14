<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    //
    protected $fillable = [
        'province',
        'city',
        'district',
        'address',
        'zip',
        'contact_name',
        'contact_phone',
        'last_used_at'
    ];
    //定义字段是时间日期类型，$address->last_used_at返回时间日期对象（Carbon对象，laravel默认时间日期处理类）
    protected $dates = ['last_used_at'];

    /**
     * 反向一对多（User->UserAddress）
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(){
        return $this->belongsTo(User::class);
    }

    /**
     * 获取完成地址访问器
     * @return string 完整地址
     */
    public function getFullAddressAttribute(){
        return "{$this->province}{$this->city}{$this->district}{$this->address}";
    }

}
