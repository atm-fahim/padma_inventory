<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\PurchasesItemRepo;
use Illuminate\Routing\Controller as BaseController;
use App\Interfaces\Repo\Backend\BranchRepo;
use App\Interfaces\Repo\Backend\ItemsRepo;
use App\Interfaces\Repo\Backend\PurchasesItemReturnRepo;
use App\Interfaces\Repo\Backend\PurchasesInvoiceReturnRepo;
use App\Interfaces\Repo\Backend\SizeRepo;
use App\Interfaces\Repo\Backend\SupplierRepo;
use App\Traits\UserAccessTraits;
use App\Interfaces\Repo\Backend\CategoryRepo;
use App\Traits\ImageUploadTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
//use Illuminate\Support\Facades\Artisan;

class PurchasesReturnController extends BaseController
{
    private $purchaseItmRtnRepo;
    private $purchaseItmRepo;
    private $purchaseInvRtnRepo;
    private $optr;
    private $itemRepo;
    private $sizeRepo;
    private $suppRepo;
    private $branchRepo;
    use ImageUploadTraits;
    use UserAccessTraits;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct
    (
        PurchasesItemReturnRepo    $purchaseItmRtnRepo,
        PurchasesInvoiceReturnRepo $purchaseInvRtnRepo,
        PurchasesItemRepo          $purchaseItmRepo,
        ItemsRepo                   $itemRepo,
        CategoryRepo                $optr,
        SizeRepo                    $sizeRepo,
        SupplierRepo                $suppRepo,
        BranchRepo                  $branchRepo,
    )
    {
        $this->middleware('auth');
        $this->pItmRtnRepo  = $purchaseItmRtnRepo;
        $this->pItmRepo     = $purchaseItmRepo;
        $this->pInvRtnRepo  = $purchaseInvRtnRepo;
        $this->itemRepo     = $itemRepo;
        $this->optr      = $optr;
        $this->sizeRepo     = $sizeRepo;
        $this->suppRepo     = $suppRepo;
        $this->branchRepo   = $branchRepo;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userAccessData = $this->userAccessFunction();
        $rendInvoiceNo = $this->random_token();
        $purchaseData = $this->pInvRtnRepo->purchasesReturnInvoice();
        $branchData = $this->branchRepo->withoutDeletingData();
        $catData = $this->optr->withoutDeletingData();
        $itemData = $this->itemRepo->getItemInfo();
        $sizeData = $this->sizeRepo->withoutDeletingData();
        $supplierData = $this->suppRepo->withoutDeletingData();
        return view('backend.purchase_return.purchase_return')->with('purchase_invoice', $purchaseData)
            ->with('category', $catData)
            ->with('all_item', $itemData)
            ->with('size', $sizeData)
            ->with('supplier', $supplierData)
            ->with('branch', $branchData)
            ->with('invoiceNo', $rendInvoiceNo)
            ->with('user_access', $userAccessData);
    }


    public function saveUpdate(Request $request)
    {
        $info = $this->singleInvoice($request);
        if($info==1): $this->itemDetails($request); endif;
        return Redirect::to('purchase-return');
    }

    public function purchasesUpdate(Request $request)
    {
        $invoice_no=$request->input('invoice_no');
        $info = $this->singleInvoice($request);
        if($info==1): $this->itemDetails($request);endif;
        return Redirect::to('purchase-return-invoice-details/'.$invoice_no);
    }

    private function singleInvoice($request)
    {
        $data = array();
        $validation = $this->pInvRtnRepo->checkReturnRequestValidity($request);
        if (!$validation["isValidationSuccess"]) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'error' => $validation["error"]], 400);
        }
        try {
            $totalInfo = $this->singaleTotalCalculate($request);
            $invoiceFile = $request->input('invoice');
            $id = $request->input('sngInvId');
            $data['org_id'] = auth()->user()->org_id;
            $data['purchases_type'] = $request->input('PurchasesType');
            $data['invoice_number'] = $request->input('invoice_no');
            $data['note'] = $request->input('note');
            $pdate = $request->input('purchases_invoice_date',true);
            $data['purchases_invoice_date'] = (!empty($pdate))?date('Y-m-d',strtotime($pdate)):'';
            $suppliers=$request->input('suppliers_id');
            $data['supplier_id'] =$suppliers[0];
            $data['created_by'] =auth()->user()->id;
            $data['total_unit_price'] =$totalInfo['Totalunit_price'];
            $data['total_tp_price'] =$totalInfo['TotalTpPrice'];
            $data['total_amount'] =$totalInfo['TotalSales_price'];
            $data['total_qty'] =$totalInfo['totalQty'];
            $data['purchases_date_time'] =date('Y-m-d',strtotime(now()));
            $data['updated_by'] =(!empty($id))?auth()->user()->id:0;
            $data['supplier_invoice_number'] = $request->input('supplier_invoice_number');
            $data['discount_amount'] = $request->input('supplier_discount');
            $data['branch_id'] = (!empty($request->input('branchId'))) ? $request->input('branchId') : auth()->user()->branch_id;
            if (isset($invoiceFile)):
                $imgPutPath = public_path('uploads/invoice/');
                $imgReadPath = 'uploads/thumbnail/invoice/';
                $data['invoice'] = $this->displayImage($imgPutPath, $invoiceFile, $imgReadPath);
            else:
                $data['invoice'] = '';
            endif;
           (!empty($id))?$this->pInvRtnRepo->update($id,$data):$this->pInvRtnRepo->save($data);
            return 1;
        } catch (\Exception $e) {
            dd($e);
        }
    }

    private function itemDetails($request)
    {

        $validation = $this->pItmRtnRepo->checkReturnRequestValidity($request);

        if (!$validation["isValidationSuccess"]) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'error' => $validation["error"]], 400);
        }
        try {

            $itmDtlsid = $request->input('pItemDtlsId',true);
            $org_id = auth()->user()->org_id;
            $itemIdCount = count($request->input('item_id', true));
            $suppliersId = $request->input('suppliers_id', true);
            $purchasesType = $request->input('PurchasesType', true);
            $itemId = $request->input('item_id', true);
            $branchId = (!empty($request->input('branchId'))) ? $request->input('branchId') : auth()->user()->branch_id;
            $expiry_date = $request->input('expiry_date', true);
            $qty = $request->input('qty', true);
            $vat = $request->input('vat', true);
            $tpPrice = $request->input('tp_price', true);
            $vcl = $request->input('vehicle_cost', true);
            $lbrCost = $request->input('labor_cost', true);
            $supplier_invoice_number = $request->input('supplier_invoice_number', true);
            $buyingPrice = $request->input('buying_price', true);
//            $size_of = $request->input('size_of', true);
            $unit_price = $request->input('unit_sales_price', true);
            $categoryId = $request->input('categoryId', true);
            $total_sales_price = $request->input('total_amount', true);
            $discount_percentage = $request->input('discount_percentage', true);
            $discount_amt = $request->input('discount_amount', true);
                for ($i = 0; $i < $itemIdCount; $i++) {
                    $id = null;//(isset($itmDtlsid[$i]))?$itmDtlsid[$i]:'';
                    $pdata = array(
                        'supplier_id' => $suppliersId[0],
                        'item_id' => $itemId[$i],
                        'expiry_date' => $expiry_date[$i],
                        'qty' => $qty[$i],
                        'vehicle_cost' => $vcl[$i],
                        'labor_cost' => $lbrCost[$i],
                        'buying_price' => $buyingPrice[$i],
//                        'size_of' => $size_of[$i],
                        'vat' => $vat[$i],
                        'tp_price' => $tpPrice[$i],
                        'unit_sales_price' => $unit_price[$i],
                        'created_by' => auth()->user()->id,
                        'updated_by' => (!empty($id))?auth()->user()->id:0,
                        'discount' => $discount_percentage[$i],
                        'purchases_type' => $purchasesType,
                        'discount_amount' => $discount_amt[$i],
                        'invoice_number' => $request->input('invoice_no', true),
                        'supplier_invoice_number' => $supplier_invoice_number,
                        'purchases_date' =>  $request->input('purchases_invoice_date', true),
                        'total_price' => $total_sales_price[$i],
                        'status' => 1,
                        'org_id' => $org_id,
                        'category_id' => $categoryId[$i],
                        'branch_id' => $branchId
                    );
//                    echo '<pre>';
//                    print_r($pdata);
                    (!empty($id))?$this->pItmRtnRepo->update($id,$pdata):$this->pItmRtnRepo->save($pdata);
            }
        } catch (\Exception $e) {
            echo $e;exit;
        }
    }



    public function active($invoiceNo)
    {
        $purchaseData = $this->pInvRtnRepo->purchasesInvoiceReturnByNumber($invoiceNo);
        $purchaseItemData = $this->pItmRtnRepo->purchasesItemReturnByNumber($invoiceNo);
        foreach ($purchaseItemData as $v_value) {
            $this->pItmRtnRepo->update($v_value->id, ['status' => 1]);
        }
        $this->pInvRtnRepo->update($purchaseData[0]->id, ['status' => 1]);
        return redirect('purchase-return');
    }

    public function inActive($invoiceNo)
    {
        $purchaseData = $this->pInvRtnRepo->purchasesInvoiceReturnByNumber($invoiceNo);
        $purchaseItemData = $this->pItmRtnRepo->purchasesItemReturnByNumber($invoiceNo);
        foreach ($purchaseItemData as $v_value) {
            $this->pItmRtnRepo->update($v_value->id, ['status' => 0]);
        }
        $this->pInvRtnRepo->update($purchaseData[0]->id, ['status' => 0]);
        return redirect('purchase-return');
    }

    public function delete($invoiceNo)
    {
        $purchaseData = $this->pInvRtnRepo->purchasesInvoiceReturnByNumber($invoiceNo);
        $purchaseItemData = $this->pItmRtnRepo->purchasesItemReturnByNumber($invoiceNo);
        foreach ($purchaseItemData as $v_value) {
            $this->pItmRtnRepo->update($v_value->id, ['status' => 9]);
        }
        $this->pInvRtnRepo->update($purchaseData[0]->id, ['status' => 9]);
        return redirect('purchase-return');
    }

    public function invoiceDetails($invoiceNo)
    {
        $purchaseData = $this->pInvRtnRepo->purchasesInvoiceReturnByNumber($invoiceNo);
        $purchaseItemData = $this->pItmRtnRepo->purchasesItemReturnByNumber($invoiceNo);
        return view('backend.purchase_return.purchases_invoice_return')->with('purchase_invoice', $purchaseData)
            ->with('purchase_item', $purchaseItemData);
    }

    public function purchaseEdit($invoiceNo)
    {
        $userAccessData = $this->userAccessFunction($id=22);
        $rendInvoiceNo = $this->random_token();
        $branchData = $this->branchRepo->withoutDeletingData();
        $catData = $this->optr->withoutDeletingData();
        $itemData = $this->itemRepo->getItemInfo();
        $sizeData = $this->sizeRepo->withoutDeletingData();
        $supplierData = $this->suppRepo->withoutDeletingData();


        $purchaseData = $this->pInvRtnRepo->purchasesInvoiceReturnByNumber($invoiceNo);
        $purchaseItemData = $this->pItmRtnRepo->purchasesItemReturnByNumber($invoiceNo);
        return view('backend.purchase.edit_purchase')->with('purchase_invoice', $purchaseData)
            ->with('purchase_item', $purchaseItemData)
            ->with('category', $catData)
            ->with('all_item', $itemData)
            ->with('size', $sizeData)
            ->with('supplier', $supplierData)
            ->with('branch', $branchData)
            ->with('invoiceNo', $rendInvoiceNo)
            ->with('user_access', $userAccessData);
    }
    public function purchaseItemInfo($itemId){
        $purchaseItemInfo = $this->pItmRepo->purchasesItemInfo($itemId);
        echo json_encode($purchaseItemInfo);
    }

    private function singaleTotalCalculate($request){
        $itemIdCount = count($request->input('item_id', true));
        $qty = $request->input('qty', true);
        $vat = $request->input('vat', true);
        $tpPrice = $request->input('tp_price', true);
        $vcl = $request->input('vehicle_cost', true);
        $lbrCost = $request->input('labor_cost', true);
        $buyingPrice = $request->input('buying_price', true);
        $unit_price = $request->input('unit_sales_price', true);
        $total_sales_price = $request->input('total_amount', true);
        $discount_percentage = $request->input('discount_percentage', true);
        $discount_amt = $request->input('discount_amount', true);

// Initialize totals
        $data = [
            'totalQty' => 0,
            'totalVat' => 0,
            'TotalTpPrice' => 0,
            'TotalLbrCost' => 0,
            'TotalbuyingPrice' => 0,
            'Totalunit_price' => 0,
            'TotalSales_price' => 0,
            'Totaldiscount_percentage' => 0,
            'Totaldiscount_amt' => 0,
        ];

        for ($i = 0; $i < $itemIdCount; $i++) {
            $data['totalQty'] += (float) $qty[$i];
            $data['totalVat'] += (float) $vat[$i];
            $data['TotalTpPrice'] += (float) $tpPrice[$i];
            $data['TotalLbrCost'] += (float) $lbrCost[$i];
            $data['TotalbuyingPrice'] += (float) $buyingPrice[$i];
            $data['Totalunit_price'] += (float) $unit_price[$i];
            $data['TotalSales_price'] += (float) $total_sales_price[$i];
            $data['Totaldiscount_percentage'] += (float) $discount_percentage[$i];
            $data['Totaldiscount_amt'] += (float) $discount_amt[$i];
        }

        return $data;

    }

    public function purchaseReturnList(){
        $userAccessData = $this->userAccessFunction();
//        dd($userAccessData);
        $purchaseData = $this->pInvRtnRepo->purchasesReturnInvoice();
        return view('backend.purchase_return.purchase_return_invoice_list')
            ->with('purchase_invoice_list', $purchaseData)
            ->with('user_access', $userAccessData);
    }
}
