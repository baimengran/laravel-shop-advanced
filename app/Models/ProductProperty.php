<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductProperty extends Model
{
    //
    protected $fillable = [
        'name',
        'value',
    ];

    public $timestamps = false;//没有created_at和updated_at字段

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
