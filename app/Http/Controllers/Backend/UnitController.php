<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\UnitRepo;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class UnitController extends BaseController
{
    private $unitRepo;
    use UserAccessTraits;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UnitRepo $unitRepo)
    {
        $this->middleware('auth');
        $this->unitRepo = $unitRepo;
    }

    public function index()
    {
        $userAccessData = $this->userAccessFunction();
        $unitData = $this->unitRepo->withoutDeletingData();
        return view('backend.unit.unit')->with('unit', $unitData)
                                        ->with('user_access',$userAccessData);
    }

    public function saveUpdate(Request $request)
    {
        $data = array();
        $validation = $this->unitRepo->checkRequestValidity($request);
        if (!$validation["isValidationSuccess"]) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'error' => $validation["error"]], 400);
        }
        try {
            $id                 = $request->input('id');
            $data['unit']       = $request->input('unit');
            $data['branch_id']  = auth()->user()->branch_id;
            $data['slug']       = Str::slug($data['size']);
            (!empty($id))?$this->unitRepo->update($id,$data):$this->unitRepo->save($data);
            return Redirect::to('size-unit');
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function edit($id)
    {
      $sizeData=$this->unitRepo->findById($id);
      echo json_encode($sizeData);
    }

    public function active($id)
    {
        $this->unitRepo->update($id,['status'=>1]);
        return redirect('size-unit');
    }

    public function inActive($id)
    {
        $this->unitRepo->update($id,['status'=>0]);
        return redirect('size-unit');
    }

    public function delete($id)
    {
        $this->unitRepo->update($id,['status'=>9]);
        return redirect('size-unit');
    }
}
