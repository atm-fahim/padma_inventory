<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\DashBoardLogRepo;
use App\Models\CategoryModel;
use App\Models\ItemModel;
use App\Models\SupplierModel;

/**
 * Repository implementation of dashboard log report.
 * they are dashboard info
 * @develop by fahim
 * @author Padma Bank limited
 * @since 2023-10-16
 */
class DashBoardLogRepoImpl extends EloquentBaseRepository implements DashBoardLogRepo
{
    protected $model;

    public function __construct()
    {

    }

public function totalCategory(){
    return CategoryModel::where('status', '1')->count('id');
}
public function totalItem(){
    return ItemModel::where('status', '1')->count('id');
}
public function totalSupplier(){
    return SupplierModel::where('status', '1')->count('id');
}

public function getCategoryList(){
    return CategoryModel::where('status', '1')->orderBy('id', 'desc')->get();
}

public function getItemList(){
    return ItemModel::where('status', '1')->orderBy('id', 'desc')->get();
}

public function getSupplierList(){
    return SupplierModel::where('status', '1')->orderBy('id', 'desc')->get();
}

}
