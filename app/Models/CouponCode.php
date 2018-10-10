<?php

namespace App\Models;

use App\Exceptions\CouponCodeUnavailableException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CouponCode extends Model
{
    //常量定义支持的优惠卷类型
    const TYPE_FIXED = 'fixed';//
    const TYPE_PERCENT = 'percent';

    public static $typeMap = [
        self::TYPE_FIXED => '固定金额',
        self::TYPE_PERCENT => '比例',
    ];

    protected $appends = ['description'];

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'total',
        'used',
        'min_amount',
        'not_before',
        'not_after',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
    //指明这两个字段是日期类型
    protected $dates = ['not_before', 'not_after'];

    /**
     * 生成优惠码
     * @param int $length 优惠码长度
     * @return string 优惠码
     */
    public static function findAvailableCode($length = 16)
    {
        do {
            //生成一个指定长度的随机字符串，并转换成大写
            $code = strtoupper(Str::random($length));
            //如果生成的优惠码以存在，则继续循环
        } while (self::query()->where('code', $code)->exists());
        return $code;
    }

    /**
     * 后台页面去除.00字符
     * @return string
     */
    public function getDescriptionAttribute()
    {
        $str = '';
        if ($this->min_amount > 0) {
            $str = '满' . str_replace('.00', '', $this->min_amount);
        }
        if ($this->type === self::TYPE_PERCENT) {
            return $str . '优惠' . str_replace('.00', '', $this->value) . '%';
        }
        return $str . '减' . str_replace('.00', '', $this->value);
    }

    /**
     * 校验优惠卷信息
     * @param null $orderAmount
     * @throws CouponCodeUnavailableException
     */
    public function checkAvailable($orderAmount = null)
    {
        if (!$this->enabled) {
            throw new CouponCodeUnavailableException('优惠卷不存在');
        }

        if ($this->total - $this->used <= 0) {
            throw new CouponCodeUnavailableException('该优惠卷已被兑完');
        }
        if ($this->not_before && $this->not_before->gt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠卷现在还不能使用');
        }
        if ($this->not_after && $this->not_after->lt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠卷已过期');
        }

        if (!is_null($orderAmount) && $orderAmount < $this->min_amount) {
            throw new CouponCodeUnavailableException('订单金额不满足该优惠卷最低金额');
        }
    }

    /**
     * 计算订单使用优惠卷后金额
     * @param $orderAmount
     * @return mixed|string
     */
    public function getAdjustedPrice($orderAmount)
    {
        //固定金额
        if ($this->type === self::TYPE_FIXED) {
            //为了保证系统健壮性，需要订单最少为0.01元
            return max(0.01, $orderAmount - $this->value);
        }
        return number_format($orderAmount * (100 - $this->value) / 100, 2, '.', '');
    }

    /**
     * 增加、减少优惠卷用量
     * @param bool $increase true新增用量 false减少用量
     * @return int
     */
    public function changeUsed($increase = true)
    {
        if ($increase) {
            //与检查SKU库存类似，这里需要检查当前用量是否已经超过总量
            return $this->newQuery()->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
        } else {
            return $this->decrement('used');
        }
    }
}



