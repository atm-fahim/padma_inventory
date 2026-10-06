<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\CategoryRepo;
use App\Interfaces\Repo\Backend\DashBoardLogRepo;
use App\Interfaces\Repo\Backend\DepartmentRepo;
use App\Interfaces\Repo\Backend\DivisionRepo;
use App\Interfaces\Repo\Backend\DocumentRepo;
use App\Traits\FileUploadTraits;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class DepartmentController extends BaseController
{
    private $divisionRepo;
    private $depRepo;
    use FileUploadTraits;
    use UserAccessTraits;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DepartmentRepo $depRepo,DivisionRepo $divisionRepo)
    {
        $this->middleware('auth');
        $this->divisionRepo = $divisionRepo;
        $this->depRepo = $depRepo;

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userAccessData        = $this->userAccessFunction();
        $divData               = $this->divisionRepo->withoutDeletingData();
        $departmentInfo        = $this->depRepo->departmentInfo();
        return view('backend.department.department')
            ->with('department_info', $departmentInfo)
            ->with('division_info', $divData)
            ->with('user_access',$userAccessData);
    }

    public function saveUpdate(Request $request)
    {
        $data = array();
        $validation = $this->depRepo->checkRequestValidity($request);
        if (!$validation["isValidationSuccess"]) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'error' => $validation["error"]], 400);
        }
        try {
            $id                            = $request->input('id');
            $data['department_name']       = $request->input('department_name');
            $data['short_name']       = $request->input('short_name');
            $data['division_id']           = $request->input('division_id');
            $data['slug']                  = Str::slug($data['department_name']);
            $data['status']                = 1;
            (!empty($id))?$this->depRepo->update($id,$data):$this->depRepo->save($data);
            return Redirect::to('department');
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function edit($id)
    {
        $catData=$this->depRepo->findById($id);
        echo json_encode($catData);
    }
    public function departmentInfoByDivisionId($id)
    {
        $departmentInfo=$this->depRepo->departmentInfoByDivisionId($id);
        echo json_encode($departmentInfo);
    }

    public function active($id)
    {
        $this->depRepo->update($id,['status'=>1]);
        return redirect('department');
    }

    public function inActive($id)
    {
        $this->depRepo->update($id,['status'=>0]);
        return redirect('department');
    }

    public function delete($id)
    {
        $this->depRepo->update($id,['status'=>9]);
        return redirect('department');
    }

}
