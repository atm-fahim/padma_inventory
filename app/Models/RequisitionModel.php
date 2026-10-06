<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionModel extends Model
{
    protected $table = 'requisitions';
    public $timestamps = true;
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(RequisitionItemModel::class, 'requisition_id');
    }
}
