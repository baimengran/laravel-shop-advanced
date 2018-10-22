<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    //
    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';

    public static $typeMap = [
        self::TYPE_NORMAL => '普通商品',
        self::TYPE_CROWDFUNDING => '众筹商品',
    ];

    protected $fillable = [
        'title', 'description', 'image', 'on_sale',
        'rating', 'sold_count', 'review_count', 'price'
    ];

    protected $casts = [
        'on_sale' => 'boolean',//on_sale是一个布尔类型字段
    ];

    //与商品SKU关联
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    /**
     * 分类关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * 众筹关联
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function crowdfunding()
    {
        return $this->hasOne(CrowdfundingProduct::class);
    }

    /**
     * 商品属性关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function properties()
    {
        return $this->hasMany(ProductProperty::class);
    }

    /**
     * 图片url
     * @return string
     */
    public function getImageUrlAttribute()
    {
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        //图片保存在Storage目录下，执行代码
        if (Str::startsWith($this->attributes['image'], ['http://', 'https://'])) {
            return $this->attributes['image'];
        }
//        return Storage::disk('public')->url($this->attributes['image']);
        return asset('uploads/' . $this->attributes['image']);
    }


    public function getGroupedPropertiesAttribute()
    {
        //$this->properties 获取当前商品的商品属性集合（一个 Collection 对象）
        return $this->properties
            //按照属性名聚合，返回集合的key是属性名，value是包含该属性名的所有属性集合
            ->groupBy('name')
            ->map(function ($properties) {
                //使用map方法将属性集合变为属性值集合
                return $properties->pluck('value')->all();
            });
    }
}
