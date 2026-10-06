<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface DashBoardLogRepo extends EloquentRepositoryInterface{

public function totalCategory();
public function totalItem();
public function totalSupplier();
public function getCategoryList();
public function getItemList();
public function getSupplierList();
}
