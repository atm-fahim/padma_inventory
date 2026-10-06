<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryChallanModel extends Model
{
    protected $table = 'delivery_challans';
    public $timestamps = true;
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(DeliveryChallanItemModel::class, 'delivery_challan_id');
    }
}

