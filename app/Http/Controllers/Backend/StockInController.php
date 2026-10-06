<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\StockInRepo;
use App\Interfaces\Repo\Backend\ItemsRepo;
use App\Interfaces\Repo\Backend\BranchRepo;
use App\Interfaces\Repo\Backend\SupplierRepo;
use App\Interfaces\Repo\Backend\CategoryRepo;
use App\Interfaces\Repo\Backend\BrandRepo;
use App\Interfaces\Repo\Backend\ItemsInventoryRepo;
use App\Support\StockReferencePrefixBuilder;
use App\Traits\UserAccessTraits;
use App\Traits\CommonTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Routing\Controller as BaseController;

class StockInController extends BaseController
{
    private $stockInRepo;
    private $itemsRepo;
    private $branchRepo;
    private $supplierRepo;
    private $categoryRepo;
    private $brandRepo;
    private $itemsInventoryRepo;
    use UserAccessTraits;
    use CommonTraits;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        StockInRepo $stockInRepo,
        ItemsRepo $itemsRepo,
        BranchRepo $branchRepo,
        SupplierRepo $supplierRepo,
        CategoryRepo $categoryRepo,
        BrandRepo $brandRepo,
        ItemsInventoryRepo $itemsInventoryRepo
    )
    {
        $this->middleware('auth');
        $this->stockInRepo = $stockInRepo;
        $this->itemsRepo = $itemsRepo;
        $this->branchRepo = $branchRepo;
        $this->supplierRepo = $supplierRepo;
        $this->categoryRepo = $categoryRepo;
        $this->brandRepo = $brandRepo;
        $this->itemsInventoryRepo = $itemsInventoryRepo;
    }

    /**
     * Show the stock in page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userAccessData = $this->userAccessFunction();
        $itemsData = $this->itemsRepo->withoutDeletingData();
        $branchData = $this->branchRepo->withoutDeletingData();
        $supplierData = $this->supplierRepo->withoutDeletingData();
        $categoryData = $this->categoryRepo->getCategory();
        $brandData = $this->brandRepo->withoutDeletingData();
        $orgId = auth()->user()->org_id ?? null;
        $branchId = auth()->user()->branch_id ?? null;
        $setUniqNoList = $this->itemsInventoryRepo->getSetUniqNoList($orgId, $branchId);
        
        return view('backend.item.stock_in')
            ->with('itemsData', $itemsData)
            ->with('branchData', $branchData)
            ->with('supplierData', $supplierData)
            ->with('categoryData', $categoryData)
            ->with('brandData', $brandData)
            ->with('setUniqNoList', $setUniqNoList)
            ->with('user_access', $userAccessData);
    }

    /**
     * Get inventory items by item ID
     */
    public function getInventoryByItemId(Request $request)
    {
        $itemId = $request->input('item_id');
        $orgId = auth()->user()->org_id;
        $branchId = $request->input('branch_id') ?? auth()->user()->branch_id;

        if (empty($itemId)) {
            return response()->json([
                'success' => false,
                'message' => 'Item ID is required'
            ], 400);
        }

        try {
            $inventoryItems = $this->stockInRepo->getItemInventoryByItemId($itemId, $orgId, $branchId);
            
            if ($inventoryItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No inventory found for this item'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $inventoryItems
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get items by SET_UNIQ_NO for desktop, laptop, server
     */
    public function getItemsBySetUniqNo(Request $request)
    {
        $setUniqNo = $request->input('set_uniq_no');
        $orgId = auth()->user()->org_id;
        $branchId = $request->input('branch_id') ?? auth()->user()->branch_id;

        if (empty($setUniqNo)) {
            return response()->json([
                'success' => false,
                'message' => 'SET_UNIQ_NO is required'
            ], 400);
        }

        $items = $this->stockInRepo->getItemsBySetUniqNo($setUniqNo, $orgId, $branchId);
        
        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No items found for this SET_UNIQ_NO'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Save stock in
     */
    public function saveUpdate(Request $request)
    {
        $validation = $this->stockInRepo->checkRequestValidity($request);
        if (!$validation['isValidationSuccess']) {
            session()->put('error', $validation['error']);
            return Redirect::back();
        }

        DB::beginTransaction();
        try {
            $orgId = auth()->user()->org_id ?? 1;
            $toBranchId = auth()->user()->branch_id ?? 1;
            $toEmployeeId = auth()->user()->employee_id;
            
            $inventoryIds = $request->input('inventory_id', []);
            $itemIds = $request->input('item_id', []);
            $categoryIds = $request->input('category_id', []);
            $supplierIds = $request->input('supplier_id', []);
            $quantities = $request->input('quantity', []);
            $descriptions = $request->input('description', []);
            $prices = $request->input('price', []);

            $lastInventoryCode = DB::table('item_inventory')
                ->where('branch_id', $toBranchId)
                ->where('org_id', $orgId)
                ->where('status', 1)
                ->orderBy('id', 'DESC')
                ->value('set_uniq_no');

            // Extract numeric part from last code
            $lastCodeNumber = 5000;
            if (!empty($lastInventoryCode)) {
                $codeParts = explode('-', $lastInventoryCode);
                if (isset($codeParts[1]) && is_numeric($codeParts[1])) {
                    $lastCodeNumber = (int)$codeParts[1];
                }
            }

            // Generate stock in number (PBL-style body) + SI- prefix with branch/division segment
            $stockInBody = $this->generateRandomItemCode($toBranchId, $categoryIds[0] ?? 1, $lastCodeNumber);
            $stockInNoFull = StockReferencePrefixBuilder::formatStockInNumber($stockInBody, (int) $toBranchId);
            
            // Collect invoice data
            $invoiceItems = [];
        
            foreach ($itemIds as $index => $itemId) {
                $quantity = $quantities[$index] ?? 0;
                $description = $descriptions[$index] ?? '';
                $categoryId = $categoryIds[$index] ?? null;
                $supplierId = $supplierIds[$index] ?? null;
                $price = $prices[$index] ?? 0;

                if (empty($itemId) || $quantity <= 0) {
                    continue;
                }

                // Get item image from items table
                $itemInfo = DB::table('item_info')->where('id', $itemId)->first();
                $itemImage = $itemInfo->image ?? '';

                // Get RAM, Storage, Processor from request
                $ramData = $request->input("ram.$index", '');
                $storageData = $request->input("storage.$index", '');
                $processorData = $request->input("processor.$index", '');

                // Create payload for new inventory record (STOCK IN)
                $payload = [
                    'org_id' => $orgId,
                    'branch_id' => $toBranchId,
                    'item_id' => $itemId,
                    'category_id' => $categoryId,
                    'brand_id' => $request->input("brand_id.$index") ?? null,
                    'supplier_id' => $supplierId,
                    'service_warranty' => $request->input("service_warranty.$index") ?? '',
                    'warranty' => $request->input("warranty.$index") ?? '',
                    'uniq_serial_no' => $request->input("serial_no.$index") ?? '',
                    'quantity' => $quantity,
                    'unit_size' => $request->input("unit_size.$index") ?? '',
                    'set_uniq_no' => $request->input("set_uniq_no.$index") ?? '',
                    'price' => $price,
                    'vat' => $request->input("vat.$index", 0) ?? 0,
                    'tax' => $request->input("tax.$index", 0) ?? 0,
                    'description' => $description,
                    'ram' => $ramData,
                    'storage' => $storageData,
                    'processor' => $processorData,
                    'graphics' => $request->input("graphics.$index") ?? '',
                    'display' => $request->input("display.$index") ?? '',
                    'battery' => $request->input("battery.$index") ?? '',
                    'color' => $request->input("color.$index") ?? '',
                    'condition' => $request->input("condition.$index") ?? 'Good',
                    'store_type' => 'SI',
                    'warehouse' => $request->input("warehouse.$index") ?? 'IT',
                    'remarks' => $request->input("remark.$index") ?: 'STOCK-IN',
                    'created_date' => now(),
                    'created_by' => auth()->user()->id,
                    'FROM_EMPLOYEE_ID' => null,
                    'TO_EMPLOYEE_ID' => $toEmployeeId,
                    'FROM_BRANCH_ID' => null,
                    'TO_BRANCH_ID' => $toBranchId,
                    'STOCK_IN_NO' => $stockInNoFull,
                    'image' => $itemImage,
                    'status' => 1,
                ];
                
                // Save new inventory record
                $newInventoryId = $this->itemsInventoryRepo->save($payload);
                
                // Collect item data for invoice
                $categoryInfo = DB::table('category')->where('id', $categoryId)->first();
                $brandInfo = !empty($payload['brand_id']) ? DB::table('brand')->where('id', $payload['brand_id'])->first() : null;
                $supplierInfo = !empty($supplierId) ? DB::table('supplier')->where('id', $supplierId)->first() : null;
                
                $invoiceItems[] = [
                    'item' => $itemInfo,
                    'quantity' => $quantity,
                    'description' => $description,
                    'serial_no' => $payload['uniq_serial_no'],
                    'set_uniq_no' => $payload['set_uniq_no'],
                    'ram' => $ramData,
                    'storage' => $storageData,
                    'processor' => $processorData,
                    'graphics' => $payload['graphics'],
                    'display' => $payload['display'],
                    'battery' => $payload['battery'],
                    'color' => $payload['color'],
                    'brand' => $brandInfo,
                    'category' => $categoryInfo,
                    'supplier' => $supplierInfo,
                    'condition' => $payload['condition'],
                    'price' => $price,
                    'vat' => $payload['vat'],
                    'tax' => $payload['tax'],
                ];
            }

            DB::commit();
            
            // Get branch information for invoice
            $toBranchInfo = DB::table('branch')->where('id', $toBranchId)->first();
            
            // Prepare invoice data
            $invoiceData = [
                'stock_in_no' => $stockInNoFull,
                'receive_date' => date('Y-m-d'),
                'to_branch' => $toBranchInfo,
                'items' => $invoiceItems,
                'org_id' => $orgId,
            ];
            
            return view('backend.item.stock_in_invoice', compact('invoiceData'));
        } catch (\Exception $e) {
            DB::rollBack();
            session()->put('error', $e->getMessage());
            return Redirect::back()->withInput();
        }
    }

    /**
     * Show stock in list page
     */
    public function stockInList()
    {
        $userAccessData = $this->userAccessFunction();
        $orgId = auth()->user()->org_id ?? null;
        $branchId = auth()->user()->branch_id ?? null;
        
        // Get distinct STOCK_IN_NO records
        $stockInList = $this->stockInRepo->getStockInList($orgId, $branchId);
        
        return view('backend.item.stock_in_list')
            ->with('stockInList', $stockInList)
            ->with('user_access', $userAccessData);
    }

    /**
     * View invoice by STOCK_IN_NO
     */
    public function viewInvoice($stockInNo)
    {
        try {
            $orgId = auth()->user()->org_id ?? 1;
            
            // Get all items for this STOCK_IN_NO
            $inventoryItems = $this->stockInRepo->getStockInByStockInNo($stockInNo, $orgId);
            
            if ($inventoryItems->isEmpty()) {
                session()->put('error', 'No items found for this Stock In Number');
                return Redirect::back();
            }
            
            // Get the first item to extract common data
            $firstItem = $inventoryItems->first();
            $toBranchId = $firstItem->TO_BRANCH_ID ?? auth()->user()->branch_id ?? 1;
            
            // Generate receive date from first item's created_date
            $createdDate = $firstItem->created_date ?? now();
            
            // Collect invoice items
            $invoiceItems = [];
            
            foreach ($inventoryItems as $inventoryItem) {
                $itemId = $inventoryItem->item_id;
                $categoryId = $inventoryItem->category_id;
                
                // Get item info
                $itemInfo = DB::table('item_info')->where('id', $itemId)->first();
                
                // Get category info
                $categoryInfo = DB::table('category')->where('id', $categoryId)->first();
                
                // Get brand info
                $brandInfo = null;
                if (!empty($inventoryItem->brand_id)) {
                    $brandInfo = DB::table('brand')->where('id', $inventoryItem->brand_id)->first();
                }
                
                // Get supplier info
                $supplierInfo = null;
                if (!empty($inventoryItem->supplier_id)) {
                    $supplierInfo = DB::table('supplier')->where('id', $inventoryItem->supplier_id)->first();
                }
                
                $invoiceItems[] = [
                    'item' => $itemInfo,
                    'quantity' => $inventoryItem->quantity ?? 1,
                    'description' => $inventoryItem->description ?? '',
                    'serial_no' => $inventoryItem->uniq_serial_no ?? '',
                    'set_uniq_no' => $inventoryItem->set_uniq_no ?? '',
                    'ram' => $inventoryItem->ram ?? '',
                    'storage' => $inventoryItem->storage ?? '',
                    'processor' => $inventoryItem->processor ?? '',
                    'graphics' => $inventoryItem->graphics ?? '',
                    'display' => $inventoryItem->display ?? '',
                    'battery' => $inventoryItem->battery ?? '',
                    'color' => $inventoryItem->color ?? '',
                    'brand' => $brandInfo,
                    'category' => $categoryInfo,
                    'supplier' => $supplierInfo,
                    'condition' => $inventoryItem->condition ?? 'Good',
                    'price' => $inventoryItem->price ?? 0,
                    'vat' => $inventoryItem->vat ?? 0,
                    'tax' => $inventoryItem->tax ?? 0,
                ];
            }
            
            $toBranchInfo = DB::table('branch')->where('id', $toBranchId)->first();
            
            // Prepare invoice data
            $invoiceData = [
                'stock_in_no' => $stockInNo,
                'receive_date' => date('Y-m-d', strtotime($createdDate)),
                'to_branch' => $toBranchInfo,
                'items' => $invoiceItems,
                'org_id' => $orgId,
            ];
            
            return view('backend.item.stock_in_invoice', compact('invoiceData'));
        } catch (\Exception $e) {
            session()->put('error', $e->getMessage());
            return Redirect::back();
        }
    }
}
