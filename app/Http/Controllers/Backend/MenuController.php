<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\BackendMenuRepo;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class MenuController extends BaseController
{
    private $menuRepo;
    use UserAccessTraits;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BackendMenuRepo $menuRepo)
    {
        $this->middleware('auth');
        $this->menuRepo = $menuRepo;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userAccessData             = $this->userAccessFunction();
        $menuData                   = $this->menuRepo->withoutDeletingData();
        $parentMenuData             = $this->menuRepo->getParentMenu();
        return view('backend.menu.menu')->with('menu', $menuData)
                                                ->with('parent_menu',$parentMenuData)
                                                ->with('user_access',$userAccessData);
    }

    public function saveUpdate(Request $request)
    {
        $data                       = array();
        $validation                 = $this->menuRepo->checkRequestValidity($request);
        if (!$validation["isValidationSuccess"]) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'error' => $validation["error"]], 400);
        }
        try {
            $id                     = $request->input('id');
            $data['menu_name']      = $request->input('menu_name');
            $data['parent_id']      = $request->input('parent_menu');
            $data['icon']           = $request->input('menu_icon');
            $data['menu_url']       = $request->input('url');
            $data['slug']           = Str::slug($data['menu_name']);
            (isset($id) && !empty($id))?$this->menuRepo->update($id,$data):$this->menuRepo->save($data);
            return Redirect::to('backend-menu');
        } catch (\Exception $e) {
            dd($e);
        }
    }


    public function edit($id)
    {
      $menuData=$this->menuRepo->findById($id);
      echo json_encode($menuData);
    }

    public function active($id)
    {
        $this->menuRepo->update($id,['status'=>1]);
        return redirect('backend-menu');
    }

    public function inActive($id)
    {
        $this->menuRepo->update($id,['status'=>0]);
        return redirect('backend-menu');
    }

    public function delete($id)
    {
        $this->menuRepo->update($id,['status'=>9]);
        return redirect('backend-menu');
    }
}
