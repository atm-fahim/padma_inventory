<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\CategoryRepo;
use App\Interfaces\Repo\Backend\DashBoardLogRepo;
use App\Interfaces\Repo\Backend\DivisionRepo;
use App\Interfaces\Repo\Backend\DocumentRepo;
use App\Traits\FileUploadTraits;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class DivisionController extends BaseController
{
    private $divisionRepo;
    use FileUploadTraits;
    use UserAccessTraits;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DivisionRepo $divisionRepo)
    {
        $this->middleware('auth');
        $this->divisionRepo = $divisionRepo;

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userAccessData = $this->userAccessFunction();
        $divData        = $this->divisionRepo->withoutDeletingData();
        return view('backend.division.division')->with('division_info', $divData)
            ->with('user_access',$userAccessData);
    }

    public function saveUpdate(Request $request)
    {
        $data = array();
        $validation = $this->divisionRepo->checkRequestValidity($request);
        if (!$validation["isValidationSuccess"]) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'error' => $validation["error"]], 400);
        }
        try {
            $id                         = $request->input('id');
            $data['division_name']      = $request->input('division_name');
            $data['short_name']         = $request->input('short_name');
            $data['slug']               = Str::slug($data['division_name']);
            $data['status']             = 1;
            (!empty($id))?$this->divisionRepo->update($id,$data):$this->divisionRepo->save($data);
            return Redirect::to('division');
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function edit($id)
    {
        $divisionData=$this->divisionRepo->findById($id);
        echo json_encode($divisionData);
    }

    public function active($id)
    {
        $this->divisionRepo->update($id,['status'=>1]);
        return redirect('division');
    }

    public function inActive($id)
    {
        $this->divisionRepo->update($id,['status'=>0]);
        return redirect('division');
    }

    public function delete($id)
    {
        $this->divisionRepo->update($id,['status'=>9]);
        return redirect('division');
    }

}
