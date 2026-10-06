<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\BranchRepo;
use App\Models\BranchModel;
use Illuminate\Support\Facades\Validator;

/**
 * Repository implementation of branch for the branch model.
 * they are branch info
 * @develop by fahim
 * @author Padma Bank limited
 * @since 2023-08-20
 */
class BranchRepoImpl extends EloquentBaseRepository implements BranchRepo
{
    protected $model;

    public function __construct(BranchModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'branch_name' => 'required|string',
        ]);
        return $validStatus->fails() ? array('isValidationSuccess' => false, 'error' => $validStatus->errors())
            : array('isValidationSuccess' => true);
    }
    public function getBranchByName($branchName){
        return $this->model::where('branch_name', $branchName)->first();
    }

    public function withoutDeletingData()
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('branch', 'division_id')) {
            return parent::withoutDeletingData();
        }

        return $this->model::select('branch.*', 'division.division_name')
            ->leftJoin('division', 'division.id', '=', 'branch.division_id')
            ->where('branch.status', '<>', 9)
            ->orderBy('branch.branch_name')
            ->get();
    }

}
