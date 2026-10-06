<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryChallanItemModel extends Model
{
    protected $table = 'delivery_challan_items';
    public $timestamps = true;
    protected $guarded = [];

    public function challan()
    {
        return $this->belongsTo(DeliveryChallanModel::class, 'delivery_challan_id');
    }
}

