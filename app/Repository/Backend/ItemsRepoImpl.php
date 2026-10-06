<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\ItemsRepo;
use App\Models\ItemModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemsRepoImpl extends EloquentBaseRepository implements ItemsRepo
{
    protected $model;

    public function __construct(ItemModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity(Request $request)
    {
        $validStatus = Validator::make($request->all(), [
            'item_title' => 'required|string|max:255'
        ]);
        return $validStatus->fails() ? array('isValidationSuccess' => false, 'error' => $validStatus->errors())
            : array('isValidationSuccess' => true);
    }

    public function itemAllInfoByOrgId(int $orgId)
    {
        return $this->model->where('org_id', $orgId)->get();
    }

    public function itemInfoByOrgId()
    {
        return $this->model->where('status', '<>',9)->get();
    }

    public function getItemInfoByCategoryId(int $categoryId)
    {
        return $this->model->where('category_id', $categoryId)->where('status', '<>',9)->get();
    }

}
