<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;
use App\Models\MenuAccessModel;

interface MenuPermissionRepo extends EloquentRepositoryInterface
{

    public function getPermissionMenu($pid, $permission_type);

    public function findByUserType($userRoleType);

    public function menuCheckByUserTypeAndMenuId($userRoleType, $menuId);
}
