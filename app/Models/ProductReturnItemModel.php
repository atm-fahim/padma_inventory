<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReturnItemModel extends Model
{
    protected $table = 'product_return_items';
    public $timestamps = true;
    protected $guarded = [];

    public function productReturn()
    {
        return $this->belongsTo(ProductReturnModel::class, 'product_return_id');
    }
}
