<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\BranchRepo;
use App\Interfaces\Repo\Backend\UserAccessTypeRepo;
use App\Interfaces\Repo\Backend\UserRepo;
use App\Traits\FileUploadTraits;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Routing\Controller as BaseController;

class UsersController extends BaseController
{
    private $userRepo;
    private $userTypeAccessRepo;
    use FileUploadTraits;
    use UserAccessTraits;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRepo $userRepo, UserAccessTypeRepo $UATR, BranchRepo $branchRepo)
    {
        $this->middleware('auth');
        $this->userRepo = $userRepo;
        $this->userTypeAccessRepo = $UATR;
        $this->branchRepo = $branchRepo;
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
        $branchData         = $this->branchRepo->withoutDeletingData();
        $userData           = $this->userRepo->withoutDeletingData();
        return view('backend.user.users')->with('users', $userData)
                                                ->with('user_type',$userTypeData)
                                                ->with('user_access',$userAccessData)
                                                ->with('branch',$branchData);
    }

    public function saveUpdate(Request $request)
    {
        $data = array();
        $validation = $this->userRepo->checkRequestValidity($request);
        if (!$validation["isValidationSuccess"]) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'error' => $validation["error"]], 400);
        }
        try {
            $id                     = $request->input('id');
            $data['name']           = $request->input('name');
            $data['employee_id']    = $request->input('employee_id');
            $data['email']          = $request->input('email');
            $data['user_types']     = $request->input('userType');
            $data['branch_id']      = (!empty($request->input('branch_id')))?$request->input('branch_id'):1;
            $data['contact_no']     = $request->input('phone_no');
            $data['password']       = Hash::make($request->password);
            $user_img1              = $request->input('user_img1');
            $image                  = $request->file('user_img');
            if(isset($image)):
                $imgPutPath         = public_path('uploads/thumbnail/users');
                $imgReadPath        = 'uploads/thumbnail/users';
                $data['image']      = $this->thumbnailImage($imgPutPath, $image, $imgReadPath);
            else:
                $data['image']      = $user_img1;
            endif;

            (!empty($id))?$this->userRepo->update($id,$data):$this->userRepo->save($data);
            return (!empty($id))?Redirect::to('users')->withSuccess('Successfully updated..!'):Redirect::to('users')->withSuccess('Successfully submitted..!');
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function edit($id)
    {
      $userData=$this->userRepo->findById($id);
      echo json_encode($userData);
    }

    public function active($id)
    {
        $this->userRepo->update($id,['status'=>1]);
        return redirect('users');
    }

    public function inActive($id)
    {
        $this->userRepo->update($id,['status'=>0]);
        return redirect('users');
    }

    public function delete($id)
    {
        $this->userRepo->update($id,['status'=>9]);
        return redirect('users');
    }


}
