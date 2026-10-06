<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DivisionModel extends Model
{
    protected $table = 'division';
    public $timestamps = true;
    public $guarded=[];

    public function children() {

        return $this->hasMany('App\Models\DivisionModel','parent_id','id') ;

    }
}
