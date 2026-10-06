<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\UserAccessTypeRepo;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class UsersTypeController extends BaseController
{
    private $userTypeAccessRepo;
    use UserAccessTraits;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserAccessTypeRepo $UATR)
    {
        $this->middleware('auth');
        $this->userTypeAccessRepo = $UATR;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userAccessData     = $this->userAccessFunction();
        $userTypeData       = $this->userTypeAccessRepo->withoutDeletingData();
        return view('backend.user.user_type')->with('user_type',$userTypeData)
                                                ->with('user_access',$userAccessData);
    }

    public function saveUpdate(Request $request)
    {
        $data               = array();
        $validation         = $this->userTypeAccessRepo->checkRequestValidity($request);
        if (!$validation["isValidationSuccess"]) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'error' => $validation["error"]], 400);
        }
        try {
            $id                 = $request->input('id');
            $data['user_type']  = $request->input('user_type');
            $data['slug']       = Str::slug($data['user_type']);
            (!empty($id))?$this->userTypeAccessRepo->update($id,$data):$this->userTypeAccessRepo->save($data);
            return Redirect::to('user-type');
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function edit($id)
    {
      $userData=$this->userTypeAccessRepo->findById($id);
      echo json_encode($userData);
    }

    public function active($id)
    {
        $this->userTypeAccessRepo->update($id,['status'=>1]);
        return redirect('user-type');
    }

    public function inActive($id)
    {
        $this->userTypeAccessRepo->update($id,['status'=>0]);
        return redirect('user-type');
    }

    public function delete($id)
    {
        $this->userTypeAccessRepo->update($id,['status'=>9]);
        return redirect('user-type');
    }


}
