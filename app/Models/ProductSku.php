<?php

namespace App\Models;

use App\Exceptions\InternalException;
use Illuminate\Database\Eloquent\Model;

class ProductSku extends Model
{
    //
    protected $fillable = [
        'title', 'description', 'price', 'stock'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function decreaseStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('减库存不能小于0');
        }
        //$this->newQuery() 方法来获取数据库的查询构造器，ORM 查询构造器的写操作只会返回 true 或者 false 代表 SQL 是否执行成功，
        //之所以使用$this->newQuery()方法是因为$this获取的是当前ORM对象，
        //而数据库查询构造器的写操作则会返回影响的行数。
        //最终执行的 SQL 类似于 update product_skus set stock = stock - $amount where id = $id and stock >= $amount，
        //这样可以保证不会出现执行之后 stock 值为负数的情况，也就避免了超卖的问题。而且我们用了数据库查询构造器，
        //可以通过返回的影响行数来判断减库存操作是否成功，如果不成功说明商品库存不足。
        //decrement()方法是查询构建器减少操作方法
        return $this->newQuery()->where('id', $this->id)->where('stock', '>=', $amount)
            ->decrement('stock', $amount);

    }


    public function addStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('加库存不能小于0');
        }
        //increment()方法是查询构建器增加方法
        $this->increment('stock', $amount);
    }
}
