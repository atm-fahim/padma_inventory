<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    protected $table = 'category';
    public $timestamps = true;
    public $guarded=[];

    public function children() {

        return $this->hasMany('App\Models\CategoryModel','parent_id','id') ;

    }
}
