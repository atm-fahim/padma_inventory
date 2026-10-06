<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\DashBoardLogRepo;
use App\Traits\FileUploadTraits;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class DashboardLogController extends BaseController
{
    private $dblRepo;
    use FileUploadTraits;
    use UserAccessTraits;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DashBoardLogRepo $dblRepo)
    {
        $this->middleware('auth');
        $this->dblRepo = $dblRepo;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


    public function dashBoardTotalLog()
    {

      $totalCategory            = $this->dblRepo->totalCategory();
      $totalItem                = $this->dblRepo->totalItem();
      $totalSupplier            = $this->dblRepo->totalSupplier();
      $countData = array(
        'totalCategory'             => $totalCategory,
        'totalItem'             => $totalItem,
        'totalSupplier'         => $totalSupplier,
      );
      echo json_encode($countData);

    }

    public function getCategoryDetails()
    {
        $categories = $this->dblRepo->getCategoryList();
        return response()->json($categories);
    }

    public function getItemDetails()
    {
        $items = $this->dblRepo->getItemList();
        return response()->json($items);
    }

    public function getSupplierDetails()
    {
        $suppliers = $this->dblRepo->getSupplierList();
        return response()->json($suppliers);
    }

}
