<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\OrganizationRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Traits\UserAccessTraits;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;
use App\Traits\FileUploadTraits;

class OrganizationController extends BaseController
{
    private $orgRepo;
    use FileUploadTraits;
    use UserAccessTraits;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OrganizationRepo $orgRepo)
    {
        $this->middleware('auth');
        $this->orgRepo = $orgRepo;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userAccessData         = $this->userAccessFunction();
        $orgData                = $this->orgRepo->withoutDeletingData();
        return view('backend.organization.organization')->with('organization', $orgData)
                                                            ->with('user_access',$userAccessData);
    }

    public function saveUpdate(Request $request)
    {
        $data                           = array();
        $validation                     = $this->orgRepo->checkRequestValidity($request);
        if (!$validation["isValidationSuccess"]) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'error' => $validation["error"]], 400);
        }
        try {
            $id                         = $request->input('id');
            $data['org_name']           = $request->input('org_name');
            $data['address']            = $request->input('address');
            $data['phone_number']       = $request->input('phone_number');
            $thumb_img1                 = $request->input('thumb_img1');
            $image                      = $request->file('thumb_img');
            $data['slug']               = Str::slug($data['org_name']);
            if(isset($image)):
                $imgPutPath             = public_path('uploads/thumbnail/org/');
                $imgReadPath            = 'uploads/thumbnail/org/';
                $data['thumb_image']    = $this->thumbnailImage($imgPutPath, $image, $imgReadPath);
            else:
                $data['thumb_image']    = $thumb_img1;
            endif;
            (!empty($id)) ? $this->orgRepo->update($id, $data) : $this->orgRepo->save($data);
            return Redirect::to('organization');
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function edit($id)
    {
        $orgData = $this->orgRepo->findById($id);
        echo json_encode($orgData);
    }

    public function active($id)
    {
        $this->orgRepo->update($id, ['status' => 1]);
        return redirect('organization');
    }

    public function inActive($id)
    {
        $this->orgRepo->update($id, ['status' => 0]);
        return redirect('organization');
    }

    public function delete($id)
    {
        $this->orgRepo->update($id, ['status' => 9]);
        return redirect('organization');
    }
}
