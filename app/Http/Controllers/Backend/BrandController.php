<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\BrandRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Traits\UserAccessTraits;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class BrandController extends BaseController
{
    private $brandRepo;
    use UserAccessTraits;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BrandRepo $brandRepo)
    {
        $this->middleware('auth');
        $this->brandRepo = $brandRepo;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $orgId = auth()->user()->org_id;
        $branchId = auth()->user()->branch_id;
        $userAccessData = $this->userAccessFunction();
        $brandData = $this->brandRepo->withoutDeletingData();
        return view('backend.brand.brand')->with('brand', $brandData)
                                                ->with('user_access',$userAccessData);
    }

    public function saveUpdate(Request $request)
    {
        $data = array();
        $validation = $this->brandRepo->checkRequestValidity($request);
        if (!$validation["isValidationSuccess"]) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'error' => $validation["error"]], 400);
        }
        try {
            $id = $request->input('id');
            $data['org_id'] = auth()->user()->org_id;
            $data['branch_id'] = auth()->user()->branch_id;
            $data['brand_name'] = $request->input('brand_name');
            $data['slug'] = Str::slug($data['brand_name']);
            (!empty($id))?$this->brandRepo->update($id,$data):$this->brandRepo->save($data);
            return Redirect::to('brand');
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function edit($id)
    {
      $brandData=$this->brandRepo->findById($id);
      echo json_encode($brandData);
    }

    public function active($id)
    {
        $this->brandRepo->update($id,['status'=>1]);
        return redirect('brand-generic-name');
    }

    public function inActive($id)
    {
        $this->brandRepo->update($id,['status'=>0]);
        return redirect('brand-generic-name');
    }

    public function delete($id)
    {
        $this->brandRepo->update($id,['status'=>9]);
        return redirect('brand-generic-name');
    }
}
