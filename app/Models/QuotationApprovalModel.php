<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationApprovalModel extends Model
{
    protected $table = 'quotation_approvals';
    public $timestamps = true;
    protected $guarded = [];

    public function quotation()
    {
        return $this->belongsTo(QuotationModel::class, 'quotation_id');
    }
}
