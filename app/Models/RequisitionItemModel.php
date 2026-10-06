<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionItemModel extends Model
{
    protected $table = 'requisition_items';
    public $timestamps = true;
    protected $guarded = [];

    public function requisition()
    {
        return $this->belongsTo(RequisitionModel::class, 'requisition_id');
    }
}
