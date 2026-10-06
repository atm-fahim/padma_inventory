<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItemModel extends Model
{
    protected $table = 'quotation_items';
    public $timestamps = true;
    protected $guarded = [];

    public function quotation()
    {
        return $this->belongsTo(QuotationModel::class, 'quotation_id');
    }
}
