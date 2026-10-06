<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethodModel extends Model
{
    protected $table = 'payment_method';
    public $timestamps = true;
    public $guarded=[];

}
