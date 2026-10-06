<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReturnModel extends Model
{
    protected $table = 'product_returns';
    public $timestamps = true;
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(ProductReturnItemModel::class, 'product_return_id');
    }

    public function supplier()
    {
        return $this->belongsTo(SupplierModel::class, 'supplier_id');
    }
}
