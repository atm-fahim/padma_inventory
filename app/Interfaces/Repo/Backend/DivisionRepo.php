<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface DivisionRepo extends EloquentRepositoryInterface
{
    public function checkRequestValidity($request);

    public function getDivision();

    public function getDivisionInfoById($id);
    public function getDivisionId($division);
}
