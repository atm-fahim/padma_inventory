<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemModel extends Model
{
    protected $table = 'item_info';
    public $timestamps = true;
    public $guarded=[];

}
