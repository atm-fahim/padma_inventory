<?php

namespace App\Http\Controllers\Backend;


use App\Interfaces\Repo\Backend\CategoryRepo;
use App\Interfaces\Repo\Backend\ItemsRepo;
use App\Interfaces\Repo\Backend\UnitRepo;
use App\Interfaces\Repo\Backend\SupplierRepo;
use App\Traits\ImageUploadTraits;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Milon\Barcode\DNS1D;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Routing\Controller as BaseController;

class ItemController extends BaseController
{
    private $itemRepo;
    private $categoryRepo;
    private $unitRepo;
    use UserAccessTraits;
    use ImageUploadTraits;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct
    (
        ItemsRepo    $itemRepo,
        UnitRepo     $unitRepo,
        CategoryRepo $categoryRepo
    )
    {
        $this->middleware('auth');
        $this->itemRepo = $itemRepo;
        $this->categoryRepo = $categoryRepo;
        $this->unitRepo = $unitRepo;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
//        session()->forget('eMessage');
        $orgId = auth()->user()->org_id??1;
        $branchId = auth()->user()->branch_id;
        $userAccessData = $this->userAccessFunction();
        $catData = $this->categoryRepo->getCategory();
        $itemData = $this->itemRepo->withoutDeletingData();
        $sizeData = $this->unitRepo->withoutDeletingData();
        return view('backend.item.item')->with('item', $itemData)
            ->with('catData', $catData)
            ->with('size', $sizeData)
            ->with('user_access', $userAccessData);
    }

    public function saveUpdate(Request $request)
    {
        $data = array();
        $validation = $this->itemRepo->checkRequestValidity($request);
        if (!$validation["isValidationSuccess"]) {
            session()->put('error', $validation["error"]);
            return Redirect::back();
        }
        try {
            $id = $request->input('id');
            $data['org_id'] = auth()->user()->org_id;
            $data['branch_id'] = auth()->user()->branch_id;
            $data['item_title'] = $request->input('item_title');
            $data['category_id'] = $request->input('category_id');
            $itemCode = $request->input('item_code');
            $data['slug'] = Str::slug($data['item_title']);
            $item_img1 = $request->input('item_img1');
            $data['status'] =1;
            $image = $request->file('item_img');
            if (isset($image)):
                $imgPutPath = public_path('uploads/item');
                $imgReadPath = 'uploads/item';
                $data['image'] = $this->thumbnilImage($imgPutPath, $image, $imgReadPath);
            else:
                $data['image'] = $item_img1;
            endif;
//            $exist = $this->itemRepo->findByItemCode($data['code']);
//            if(isset($exist) && !empty($exist)){
//                $error = 'item code already exist..!';
//                session()->put('eMessage',$error);
//                return Redirect::to('items');
//            }
            (!empty($id)) ? $this->itemRepo->update($id, $data) : $this->itemRepo->save($data);
            return Redirect::to('items');
        } catch (\Exception $e) {
            $error = $e->getMessage();
            session()->put('eMessage', $error);
            return back();
        }
    }

    private function itemCodeGenerate($supplier_id, $brand_id, $category_id, $itemCode): string
    {
        $supplierName = $this->suppRepo->getSupplierInfoById($supplier_id);
        $brandName = $this->brandRepo->getBrandInfoById($brand_id);
        $categoryName = $this->optr->getCategoryInfoById($category_id);
        $lastCode = $this->itemRepo->getLastCode();
        $code = (!empty($lastCode)) ? $lastCode['code'] : null;
        return (!empty($itemCode)) ? $itemCode : $this->generateRandomItemCode($supplierName['supplier_name'], $brandName['brand_name'], $categoryName['category'], $code);
    }

    public function edit($id)
    {
        $itemData = $this->itemRepo->findById($id);
        return json_encode($itemData);
    }

    public function itemsByCategory($categoryId)
    {
        $itemData = $this->itemRepo->getItemInfoByCategoryId($categoryId);
        return json_encode($itemData);
    }

    public function itemInfoById($id)
    {
        $itemData = $this->itemRepo->findById($id);
        return json_encode($itemData);
    }


    public function brandWiseItemById($id)
    {
        $itemInfo = $this->itemRepo->brandWiseItemById($id);
        return json_encode($itemInfo);
    }

    public function showBarcode()
    {
        return redirect()->route('barcode-generate');
    }
    public function barcodeGenerate(Request $request)
    {
        $userAccessData = $this->userAccessFunction();
        $item_info = $this->itemRepo->itemAllInfoByOrgId(auth()->user()->org_id);
        $item_id = $request->input('item_id');
        $qty = $request->input('qty');
        $custom_price = $request->input('custom_price');
        $brand = $request->input('brand');
        $price = $request->input('price');
        $p_code = $request->input('p_code');
        $product_name = $request->input('product_name');
        $itemData = $this->itemRepo->itemAInfoByItemId($item_id);
        $is_price =0;
       if(isset($price) && !empty($custom_price)){$is_price=$custom_price;}
       else if(isset($price) && empty($is_price) && !empty($itemData->sales_price)){$is_price=$itemData->sales_price; }else{$is_price=0;}
       $product = [
            'name' => (isset($product_name) && !empty($itemData->item))?$itemData->item:'N/A',
            'price' => ($is_price)?$is_price:0.00,
            'code' => (isset($p_code) && !empty($itemData->code))?$itemData->code:0000,
            'brand_name' => (isset($brand))?$itemData->brand_name:'',
        ];
        $generatorPNG = new BarcodeGeneratorPNG();
        $barcodeData = $generatorPNG->getBarcode($product['code'], $generatorPNG::TYPE_CODE_128,2,50);
        $base64 = base64_encode($barcodeData);
        $brimg = '<img src="data:image/png;base64,' . $base64 . '" />';
        return view('backend.item.barcode_generate')
            ->with('product_info',$product)
            ->with('barcode',$brimg)
            ->with('item_info', $item_info)
            ->with('barcodeQty', $qty)
            ->with('user_access', $userAccessData);
    }

    public function itemInfoBySupplierId($supplierId)
    {
        $itemData = $this->itemRepo->findBySupplierId($supplierId);
        return response()->json($itemData);
//        echo json_encode($itemData);exit;
    }

    public function getItemInfoByItemCode($code)
    {
        $itemData = $this->itemRepo->findByItemCode($code);
        echo json_encode($itemData);
        exit;
    }

    public function active($id)
    {
        $this->itemRepo->update($id, ['status' => 1]);
        return redirect('items');
    }

    public function inActive($id)
    {
        $this->itemRepo->update($id, ['status' => 0]);
        return redirect('items');
    }

    public function delete($id)
    {
        $this->itemRepo->update($id, ['status' => 9]);
        return redirect('items');
    }
}
