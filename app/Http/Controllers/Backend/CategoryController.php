<?php

namespace App\Http\Controllers\Backend;


use App\Interfaces\Repo\Backend\CategoryRepo;
use App\Traits\FileUploadTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Traits\UserAccessTraits;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class CategoryController extends BaseController
{
    private $catRepo;
    use FileUploadTraits;
    use UserAccessTraits;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CategoryRepo $catRepo)
    {
        $this->middleware('auth');
        $this->catRepo = $catRepo;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userAccessData = $this->userAccessFunction();
        $parentCategory = $this->catRepo->getCategory();
        $catDataInfo        = $this->catRepo->withoutDeletingData();
        return view('backend.category.category')
        ->with('catDataInfo', $catDataInfo)
        ->with('parentCategory', $parentCategory)
        ->with('user_access',$userAccessData);
    }

    public function saveUpdate(Request $request)
    {
        $data = array();
        $validation = $this->catRepo->checkRequestValidity($request);
        if (!$validation["isValidationSuccess"]) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'error' => $validation["error"]], 400);
        }
        try {
            $id                         = $request->input('id');
            $data['category_title']           = $request->input('category_title');
            $data['parent_id']              = $request->input('parent_id')??0;
            $data['slug']               = Str::slug($data['category_title']);
            $data['status']             = 1;
            (!empty($id))?$this->catRepo->update($id,$data):$this->catRepo->save($data);
            return Redirect::to('category');
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function edit($id)
    {
      $catData=$this->catRepo->findById($id);
      echo json_encode($catData);
    }

    public function active($id)
    {
        $this->catRepo->update($id,['status'=>1]);
        return redirect('category');
    }

    public function inActive($id)
    {
        $this->catRepo->update($id,['status'=>0]);
        return redirect('category');
    }

    public function delete($id)
    {
        $this->catRepo->update($id,['status'=>9]);
        return redirect('category');
    }

    /**
     * Import categories from uploaded Excel/CSV file.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $this->catRepo->importFromExcel($request->file('file'));

        return Redirect::to('category');
    }
}
