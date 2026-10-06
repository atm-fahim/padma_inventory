<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationModel extends Model
{
    protected $table = 'quotations';
    public $timestamps = true;
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(QuotationItemModel::class, 'quotation_id');
    }

    public function supplier()
    {
        return $this->belongsTo(SupplierModel::class, 'supplier_id');
    }

    public function approvals()
    {
        return $this->hasMany(QuotationApprovalModel::class, 'quotation_id');
    }
}
