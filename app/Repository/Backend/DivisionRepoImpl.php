<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\DivisionRepo;
use App\Models\DivisionModel;
use Illuminate\Support\Facades\Validator;

/**
 * Repository implementation of division for the division model.
 * they are division info
 * @develop by fahim
 * @author Padma Bank limited
 * @since 2023-11-08
 */
class DivisionRepoImpl extends EloquentBaseRepository implements DivisionRepo
{


    protected $model;

    public function __construct(DivisionModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'division_name' => 'required|string',
            'short_name' => 'required|string',
//            'file' => 'required|image|mimes:jpg,jpeg,png,gif,svg|max:200',
        ]);
        return $validStatus->fails() ? array('isValidationSuccess' => false, 'error' => $validStatus->errors())
            : array('isValidationSuccess' => true);
    }

    public function getDivision()
    {
        return $this->model::with('children')->where('parent_id', 0)->get();
    }

    public function getDivisionInfoById($id)
    {
        return $this->model::where('id', $id)->first();
    }

    public function getDivisionId($division)
    {
        return $this->model::where('slug', $division)->first();
    }
}
