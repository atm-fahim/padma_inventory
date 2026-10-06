<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemsInventoryModel extends Model
{
    protected $table = 'item_inventory';
    public $timestamps = true;
    public $guarded=[];

}
