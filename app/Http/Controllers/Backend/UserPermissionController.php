<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\MenuPermissionRepo;
use App\Interfaces\Repo\Backend\UserAccessTypeRepo;
use App\Traits\FileUploadTraits;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class UserPermissionController extends BaseController
{
    private $menuPermissionRepo;
    private $userTypeAccessRepo;
    use FileUploadTraits;
    use UserAccessTraits;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MenuPermissionRepo $MPR, UserAccessTypeRepo $UATR)
    {
        $this->middleware('auth');
        $this->menuPermissionRepo = $MPR;
        $this->userTypeAccessRepo = $UATR;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userAccessData = $this->userAccessFunction();
        $accessTypeData = $this->userTypeAccessRepo->withoutDeletingData();
        return view('backend.menu_permission.menu_permission')
            ->with('user_access', $userAccessData)
            ->with('access_type', $accessTypeData);
    }

    public function getPermissionMenu($permission_type)
    {
        $data = [];
        $parent_menu = $this->menuPermissionRepo->getPermissionMenu(0, $permission_type);
        $i = 1;
        foreach ($parent_menu as $v_data) {
            echo "<tr>
                    <td>" . $i++ . "</td>
                        <td colspan='7'>
                        <input class='allchk' style='margin-left: 50%;display: none;' name='menu_id[]' type='checkbox'
                   value='" . $v_data->id . "'";
            if ($v_data->is_create == 1) {
                echo 'checked=checked';
            }
            echo " />
                        <input name='is_create_" . $v_data->id . "[]' class='crchk chksuball' data-id='" . $v_data->id . "' id='main_" . $v_data->id . "'
                            type='checkbox' value='1'";
            if ($v_data->is_create == 1) {
                echo 'checked=checked';
            }
            echo " />&nbsp;
                        <strong>" . $v_data->menu_name . "</strong>
                    </td>
                  </tr>";
            $submenu = $this->menuPermissionRepo->getPermissionMenu($v_data->id, $permission_type);
            foreach ($submenu as $v_sub_menu) {
                echo "<tr>
                        <td>" . $i++ . "</td>
                            <td class='menu'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input name='menu_id[]' data-id='" . $v_sub_menu->parent_id . "' class='allchk sub_" . $v_sub_menu->parent_id . " CheckMaster' type='checkbox' value='" . $v_sub_menu->id . "'";
                if ($v_sub_menu->is_create == 1 or $v_sub_menu->is_edit == 1 or $v_sub_menu->is_delete == 1 or $v_sub_menu->is_view == 1) {
                    echo "checked=checked";
                }
                echo ">&nbsp;" . $v_sub_menu->menu_name . "
                        </td>
                        <td>
                            <input class='crchk sub_pr_" . $v_sub_menu->parent_id . "' data-id='" . $v_sub_menu->parent_id . "' name='is_create_" . $v_sub_menu->id . "[]' type='checkbox' value='1'";
                if ($v_sub_menu->is_create == 1) {
                    echo 'checked=checked';
                }
                echo " />
                        </td>
                        <td>
                            <input class='editchk sub_pr_" . $v_sub_menu->parent_id . "' data-id='" . $v_sub_menu->parent_id . "' name='is_edit_" . $v_sub_menu->id . "[]' type='checkbox' value='1'";
                if ($v_sub_menu->is_edit == 1) {
                    echo 'checked=checked';
                }
                echo "/>
                        </td>
                        <td>
                           <input class='deletechk sub_pr_" . $v_sub_menu->parent_id . "' data-id='" . $v_sub_menu->parent_id . "' name='is_delete_" . $v_sub_menu->id . "[]' type='checkbox' value='" . $v_sub_menu->id . "'";
                if ($v_sub_menu->is_delete == 1) {
                    echo 'checked=checked';
                }
                echo ">
                        </td>
                        <td>
                             <input class='viewchk sub_pr_" . $v_sub_menu->parent_id . "' data-id='" . $v_sub_menu->parent_id . "'
                                name='is_view_" . $v_sub_menu->id . "[]' type='checkbox'
                                value='1'";
                if ($v_sub_menu->is_view == 1) {
                    echo 'checked=checked';
                }
                echo ">
                        </td>
                        <td>
                             <input class='cancelChk sub_pr_" . $v_sub_menu->parent_id . "' data-id='" . $v_sub_menu->parent_id . "'
                                name='is_cancel_" . $v_sub_menu->id . "[]' type='checkbox'
                                value='1'";
                if ($v_sub_menu->is_cancel == 1) {
                    echo 'checked=checked';
                }
                echo ">
                        </td>
                        <td>
                             <input class='reqchk sub_pr_" . $v_sub_menu->parent_id . "' data-id='" . $v_sub_menu->parent_id . "'
                                name='is_request_" . $v_sub_menu->id . "[]' type='checkbox'
                                value='1'";
                if ($v_sub_menu->is_request == 1) {
                    echo 'checked=checked';
                }
                echo ">
                        </td>
                        <td>
                             <input class='appchk sub_pr_" . $v_sub_menu->parent_id . "' data-id='" . $v_sub_menu->parent_id . "'
                                name='is_approved_" . $v_sub_menu->id . "[]' type='checkbox'
                                value='1'";
                if ($v_sub_menu->is_approved == 1) {
                    echo 'checked=checked';
                }
                echo ">
                        </td>
                </tr>";
            }
        }
        return view('backend.menu_permission.menu_permission_js');
    }

    public function givePermission(Request $request)
    {
        $menu_id = count($request->menu_id);
        $org_id = auth()->user()->org_id;
        $userRoleType = $request->user_role_types;
        $menuId = $request->menu_id;
        $roleCheck = $this->menuPermissionRepo->findByUserType($userRoleType);
        if (isset($roleCheck['0']['menu_id']) && !empty($roleCheck['0']['menu_id'])) {
            foreach ($roleCheck as $v_m) {
                $id = $v_m->id;
                $m['is_create'] = 0;
                $m['is_edit'] = 0;
                $m['is_delete'] = 0;
                $m['is_view'] = 0;
                $m['is_cancel'] = 0;
                $m['is_request'] = 0;
                $m['is_approved'] = 0;
                $m['status'] = 1;
                $this->menuPermissionRepo->update($id, $m);
            }
        }
        for ($i = 0; $i < $menu_id; $i++) {
            $mid = $menuId[$i];
            $isCreate = $request->input('is_create_' . $mid);
            if (isset($isCreate) && !empty($isCreate)) {
                $isCreate = '1';
            } else {
                $isCreate = '0';
            }
            $isedit = $request->input('is_edit_' . $mid);
            if (isset($isedit) && !empty($isedit)) {
                $isedit = '1';
            } else {
                $isedit = '0';
            }
            $isDelete = $request->input('is_delete_' . $mid);
            if (isset($isDelete) && !empty($isDelete)) {
                $isDelete = '1';
            } else {
                $isDelete = '0';
            }
            $isView = $request->input('is_view_' . $mid);
            if (isset($isView) && !empty($isView)) {
                $isView = '1';
            } else {
                $isView = '0';
            }
            $isCancel = $request->input('is_cancel_' . $mid);
            if (isset($isCancel) && !empty($isCancel)) {
                $isCancel = '1';
            } else {
                $isCancel = '0';
            }

            $isRequest = $request->input('is_request_' . $mid);
            if (isset($isRequest) && !empty($isRequest)) {
                $isRequest = '1';
            } else {
                $isRequest = '0';
            }

            $isApproved = $request->input('is_approved_' . $mid);
            if (isset($isApproved) && !empty($isApproved)) {
                $isApproved = '1';
            } else {
                $isApproved = '0';
            }
            $mdata = array(
                'menu_id' => $menuId[$i],
                'is_create' => $isCreate,
                'is_edit' => $isedit,
                'is_delete' => $isDelete,
                'is_view' => $isView,
                'is_cancel' => $isCancel,
                'is_request' => $isRequest,
                'is_approved' => $isApproved,
                'user_type' => $userRoleType[0],
                'status' => '1',
                'org_id' => $org_id,
            );
            $menu_chk = $this->menuPermissionRepo->menuCheckByUserTypeAndMenuId($userRoleType, $menuId[$i]);
            if (isset($menu_chk['id']) && !empty($menu_chk['id'])) {
                $this->menuPermissionRepo->update($menu_chk['id'], $mdata);
            } else {
                $this->menuPermissionRepo->save($mdata);
            }
        }
        return redirect('permission');
    }

    //**** Find Location with Latitude and Longitude
    public function radians(Request $request)
    {
        $latitude = LATTITUDE_GOES_HERE;
        $longtitude = LONGITUDE_GOES_HERE;
        $showResult = DB::table("users")
            ->select("users.id"
                , DB::raw("55555 * acos(cos(radians(" . $latitude . "))
                * cos(radians(users.lat))
                * cos(radians(users.lon) - radians(" . $longtitude . "))
                + sin(radians(" . $latitude . "))
                * sin(radians(users.lat))) AS distance"))
            ->groupBy("users.id")
            ->get();

        dd($showResult);
    }

    public function edit($id)
    {
        $brandData = $this->branchRepo->findById($id);
        echo json_encode($brandData);
    }

    public function active($id)
    {
        $this->branchRepo->update($id, ['status' => 1]);
        return redirect('branch');
    }

    public function inActive($id)
    {
        $this->branchRepo->update($id, ['status' => 0]);
        return redirect('branch');
    }

    public function delete($id)
    {
        $this->branchRepo->update($id, ['status' => 9]);
        return redirect('branch');
    }
}
