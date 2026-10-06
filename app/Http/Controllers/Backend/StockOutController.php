<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\StockOutRepo;
use App\Interfaces\Repo\Backend\ItemsRepo;
use App\Interfaces\Repo\Backend\BranchRepo;
use App\Interfaces\Repo\Backend\ItemsInventoryRepo;
use App\Interfaces\Repo\Backend\DivisionRepo;
use App\Support\StockReferencePrefixBuilder;
use App\Traits\UserAccessTraits;
use App\Traits\CommonTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Routing\Controller as BaseController;

class StockOutController extends BaseController
{
    private $stockOutRepo;
    private $itemsRepo;
    private $branchRepo;
    private $itemsInventoryRepo;
    private $divisionRepo;
    use UserAccessTraits;
    use CommonTraits;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        StockOutRepo $stockOutRepo,
        ItemsRepo $itemsRepo,
        BranchRepo $branchRepo,
        ItemsInventoryRepo $itemsInventoryRepo,
        DivisionRepo $divisionRepo
    )
    {
        $this->middleware('auth');
        $this->stockOutRepo = $stockOutRepo;
        $this->itemsRepo = $itemsRepo;
        $this->branchRepo = $branchRepo;
        $this->itemsInventoryRepo = $itemsInventoryRepo;
        $this->divisionRepo = $divisionRepo;
    }

    /**
     * Show the stock out page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userAccessData = $this->userAccessFunction();
        $itemsData = $this->itemsRepo->withoutDeletingData();
        $branchData = $this->branchRepo->withoutDeletingData();
        $orgId = auth()->user()->org_id ?? null;
        $defaultFromBranchId = auth()->user()->branch_id ?? null;
        $fromBranchForList = old('from_branch_id', $defaultFromBranchId);
        $setUniqNoList = $this->itemsInventoryRepo->getSetUniqNoList($orgId, $fromBranchForList);
        return view('backend.item.stock_out')
            ->with('itemsData', $itemsData)
            ->with('branchData', $branchData)
            ->with('setUniqNoList', $setUniqNoList)
            ->with('defaultFromBranchId', $defaultFromBranchId)
            ->with('user_access', $userAccessData);
    }

    /**
     * SET_UNIQ_NO dropdown options for the selected source branch (stock-out).
     */
    public function getBranchSetUniqNoList(Request $request)
    {
        $branchId = $request->input('branch_id');
        $orgId = auth()->user()->org_id ?? null;

        if ($branchId === null || $branchId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Branch is required',
            ], 400);
        }

        $list = $this->itemsInventoryRepo->getSetUniqNoList($orgId, $branchId);

        return response()->json([
            'success' => true,
            'data' => $list,
        ]);
    }

    /**
     * Get employee information by employee ID
     */
    public function getEmployeeByEmployeeId(Request $request)
    {
        $employeeId = $request->input('employee_id');
        
        if (empty($employeeId)) {
            return response()->json([
                'success' => false,
                'message' => 'Employee ID is required'
            ], 400);
        }
        $endPointUrl="/api/v3/user-info-padmaportal?emp_id=".$employeeId;
        $employee = json_decode($this->employeeInfo($endPointUrl));        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }
       
        // Get hr_branch_id from branch table based on employee's branch_id
        $hrBranchId = null;
        if (isset($employee->branch_id)) {
            $branch = DB::table('branch')
                ->where('hr_branch_id', $employee->branch_id)
                ->first();
            $hrBranchId = $branch->hr_branch_id ?? $employee->branch_id;
        }
       
        return response()->json([
            'success' => true,
            'data' => [
                'name' => $employee->emp_name,
                'employee_id' => $employee->employee_id,
                'branch_id' => $hrBranchId ?? $employee->branch_id,
                'branch_name' => $employee->branch_name ?? ''
            ]
        ]);
    }

    /**
     * Get inventory items by item ID
     */
    public function getInventoryByItemId(Request $request)
    {
        $itemId = $request->input('item_id');
        $orgId = auth()->user()->org_id;
        $branchId = $request->filled('from_branch_id')
            ? $request->input('from_branch_id')
            : ($request->filled('branch_id') ? $request->input('branch_id') : auth()->user()->branch_id);

        if (empty($itemId)) {
            return response()->json([
                'success' => false,
                'message' => 'Item ID is required'
            ], 400);
        }

        try {
            $inventoryItems = $this->stockOutRepo->getItemInventoryByItemId($itemId, $orgId, $branchId);
          
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
        $branchId = $request->filled('from_branch_id')
            ? $request->input('from_branch_id')
            : ($request->filled('branch_id') ? $request->input('branch_id') : auth()->user()->branch_id);

        if (empty($setUniqNo)) {
            return response()->json([
                'success' => false,
                'message' => 'SET_UNIQ_NO is required'
            ], 400);
        }

        $items = $this->stockOutRepo->getItemsBySetUniqNo($setUniqNo, $orgId, $branchId);
        
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
     * Oracle: empty string / non-numeric text bound to NUMBER → ORA-01722.
     * HR employee IDs (11+ digits) exceed NUMBER(10) — columns should be VARCHAR2; see database/oracle_alter_item_inventory_employee_ids.sql
     */
    private function sanitizeInventoryPayloadForOracle(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if ($value === null) {
                continue;
            }
            if ($value === false) {
                $payload[$key] = null;
                continue;
            }
            if (is_string($value) && trim($value) === '') {
                $payload[$key] = null;
            }
        }

        $intNullable = [
            'supplier_id', 'brand_id', 'category_id', 'item_id', 'org_id', 'branch_id',
            'from_branch_id', 'to_branch_id', 'created_by',
        ];
        foreach ($intNullable as $k) {
            if (!array_key_exists($k, $payload) || $payload[$k] === null) {
                continue;
            }
            $v = $payload[$k];
            if ($v === '' || (is_string($v) && trim($v) === '')) {
                $payload[$k] = null;
                continue;
            }
            if (is_string($v) && !is_numeric(trim($v))) {
                $payload[$k] = null;
                continue;
            }
            $payload[$k] = (int) $v;
        }

        foreach (['price', 'vat', 'tax'] as $k) {
            if (!array_key_exists($k, $payload) || $payload[$k] === null) {
                continue;
            }
            $v = $payload[$k];
            if ($v === '' || (is_string($v) && trim($v) === '')) {
                $payload[$k] = 0;
                continue;
            }
            $payload[$k] = is_numeric($v) ? (float) $v : 0;
        }

        if (array_key_exists('quantity', $payload) && $payload['quantity'] !== null) {
            $payload['quantity'] = (int) $payload['quantity'];
        }
        if (array_key_exists('status', $payload) && $payload['status'] !== null) {
            $payload['status'] = (int) $payload['status'];
        }

        // Often defined as NUMBER in Oracle; free text like "1 Year" must not be bound as number
        foreach (['service_warranty', 'warranty'] as $k) {
            if (!array_key_exists($k, $payload) || $payload[$k] === null) {
                continue;
            }
            $v = is_string($payload[$k]) ? trim($payload[$k]) : $payload[$k];
            if ($v === '' || $v === null) {
                $payload[$k] = null;
            } elseif (is_numeric($v)) {
                $payload[$k] = (strpos((string) $v, '.') !== false) ? (float) $v : (int) $v;
            } else {
                $payload[$k] = null;
            }
        }

        foreach (['from_employee_id', 'to_employee_id'] as $k) {
            if (!array_key_exists($k, $payload) || $payload[$k] === null) {
                continue;
            }
            $v = is_string($payload[$k]) ? trim($payload[$k]) : $payload[$k];
            if ($v === '' || $v === null) {
                $payload[$k] = null;
            } else {
                $payload[$k] = (string) $v;
            }
        }

        return $payload;
    }

    /**
     * Build a unique stock-out number for each submission.
     */
    private function generateUniqueStockOutNo(int $fromBranchId, int $toBranchId): string
    {
        $prefix = StockReferencePrefixBuilder::formatStockOutPrefix($fromBranchId, $toBranchId);

        $lastStockOutNo = DB::table('item_inventory')
            ->where('stock_out_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('stock_out_no');

        $nextSerial = 1;
        if (!empty($lastStockOutNo) && preg_match('/-(\d{4})$/', $lastStockOutNo, $matches)) {
            $nextSerial = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextSerial, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Save stock out
     */
    public function saveUpdate(Request $request)
    {
        $validation = $this->stockOutRepo->checkRequestValidity($request);
        if (!$validation['isValidationSuccess']) {
            session()->flash('error', $validation['error']);
            return Redirect::back();
        }

        DB::beginTransaction();
        try {
            $orgId = auth()->user()->org_id ?? 1;
            $fromBranchId = (int) $request->input('from_branch_id', auth()->user()->branch_id ?? 1);
            $fromEmployeeId = auth()->user()->employee_id;
            
            $inventoryIds = $request->input('inventory_id', []);
            $itemIds = $request->input('item_id', []);
            $categoryIds = $request->input('category_id', []);
            $quantities = $request->input('quantity', []);
            $descriptions = $request->input('description', []);
            $globalNote = trim((string)$request->input('global_note', ''));
            $toEmployeeIds = $request->input('to_employee_id', []);
            $toBranchIds = $request->input('to_branch_id', []);

            $toBranchForNo = 0;
            foreach ($toBranchIds as $tb) {
                if ($tb !== null && $tb !== '') {
                    $toBranchForNo = (int) $tb;
                    break;
                }
            }
            if ($toBranchForNo <= 0) {
                $toBranchForNo = $fromBranchId;
            }

            // Generate unique stock out number for this transaction
            $fullStockOutNo = $this->generateUniqueStockOutNo($fromBranchId, $toBranchForNo);
            
            // Collect invoice data
            $invoiceItems = [];
            $gatePassCode = date('YmdHis') . '-' . $fromBranchId;
        
            foreach ($inventoryIds as $index => $inventoryId) {
                $quantity = $quantities[$index] ?? 0;
                $description = $descriptions[$index] ?? '';
                $itemId = $itemIds[$index] ?? null;
                $categoryId = $categoryIds[$index] ?? null;
                $toEmployeeId = $toEmployeeIds[$index] ?? '';
                $toBranchId = $toBranchIds[$index] ?? '';

                if (empty($inventoryId) || $quantity <= 0 || empty($itemId)) {
                    continue;
                }
                
                // Get original inventory item
                $inventoryItem = $this->stockOutRepo->findById($inventoryId);

                if (!$inventoryItem || $inventoryItem->quantity < $quantity) {
                    throw new \Exception("Insufficient quantity for inventory ID: $inventoryId");
                }

                if ((int) ($inventoryItem->branch_id ?? 0) !== $fromBranchId) {
                    throw new \Exception('Selected inventory does not belong to the source branch you chose.');
                }

                // Update original inventory quantity (reduce from source)

                // $newQuantity = $inventoryItem->quantity - $quantity;
                // $this->stockOutRepo->update($inventoryId, [
                //     'quantity' => $newQuantity
                // ]);

                // Get item image from items table
                $itemInfo = DB::table('item_info')->where('id', $itemId)->first();
                $itemImage = $itemInfo->image ?? '';

                // Get RAM, Storage, Processor from inventory item or request
                $ramData = $inventoryItem->ram ?? $request->input("ram.$index", '');
                $storageData = $inventoryItem->storage ?? $request->input("storage.$index", '');
                $processorData = $inventoryItem->processor ?? $request->input("processor.$index", '');

                // Get item_inventory_id from request
                $id = $request->input("inventory_id.$index");
                
                // If item_inventory_id exists, update the existing record
                if (!empty($id)) {
                    $data = [
                        'status' => 2,
                    ];
                    $this->itemsInventoryRepo->update($id, $data);
                } 
                    // Create payload for new inventory record (STOCK OUT)
                    $payload = [
                        'org_id' => $orgId,
                        'branch_id' => $toBranchId ?: $fromBranchId,
                        'item_id' => $itemId,
                        'category_id' => $categoryId ?: $inventoryItem->category_id,
                        'brand_id' => $request->input("brand_id.$index") ?: $inventoryItem->brand_id,
                        'supplier_id' => $request->input("supplier_id.$index") ?: $inventoryItem->supplier_id,
                        'service_warranty' => $request->input("service_warranty.$index") ?: $inventoryItem->service_warranty,
                        'warranty' => $request->input("warranty.$index") ?: $inventoryItem->warranty,
                        'uniq_serial_no' => $request->input("serial_no.$index") ?: $inventoryItem->uniq_serial_no,
                        'quantity' => $quantity,
                        'unit_size' => $request->input("unit_size.$index") ?: $inventoryItem->unit_size,
                        'set_uniq_no' => $request->input("set_uniq_no.$index") ?: $inventoryItem->set_uniq_no,
                        'price' => $request->input("price.$index", 0) ?: $inventoryItem->price ?? 0,
                        'vat' => $request->input("vat.$index", 0) ?: $inventoryItem->vat ?? 0,
                        'tax' => $request->input("tax.$index", 0) ?: $inventoryItem->tax ?? 0,
                        'description' => $description,
                        'ram' => $ramData,
                        'storage' => $storageData,
                        'processor' => $processorData,
                        'graphics' => $request->input("graphics.$index") ?: $inventoryItem->graphics ?? '',
                        'display' => $request->input("display.$index") ?: $inventoryItem->display ?? '',
                        'battery' => $request->input("battery.$index") ?: $inventoryItem->battery ?? '',
                        'color' => $request->input("color.$index") ?: $inventoryItem->color ?? '',
                        'condition' => $request->input("condition.$index") ?: $inventoryItem->condition ?? '',
                        'store_type' => 'SO',
                        'warehouse' => 'IT-OUT',
                        'remarks' => 'STOCK-OUT',
                        'stock_out_note' => $globalNote !== '' ? $globalNote : null,
                        'created_date' => now(),
                        'created_by' => auth()->user()->id,
                        'from_employee_id' => $fromEmployeeId,
                        'to_employee_id' => $toEmployeeId,
                        'from_branch_id' => $fromBranchId,
                        'to_branch_id' => $toBranchId,
                        'stock_out_no' => $fullStockOutNo,
                        'image' => $itemImage,
                        'status' => 2,
                    ];

                    $payload = $this->sanitizeInventoryPayloadForOracle($payload);

                    // Save new inventory record
                    $newInventoryId = $this->itemsInventoryRepo->save($payload);
                // Create stock out record
                
            }

            DB::commit();
            
            // Redirect to stock out invoice route
            session()->flash('success', 'Stock out successfully!');
            
            // Redirect using route helper with proper parameter
            return redirect()->route('stock-out-invoice', $fullStockOutNo);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
            return Redirect::back()->withInput();
        }
    }

    /**
     * Show stock out list page
     */
    public function stockOutList()
    {
        $userAccessData = $this->userAccessFunction();
        $orgId = auth()->user()->org_id ?? null;
        $branchId = auth()->user()->branch_id ?? null;
        
        // Get distinct STOCK_OUT_NO records
        $stockOutList = $this->stockOutRepo->getStockOutList($orgId, $branchId);
        
        return view('backend.item.stock_out_list')
            ->with('stockOutList', $stockOutList)
            ->with('user_access', $userAccessData);
    }

    /**
     * View invoice by STOCK_OUT_NO
     */
    public function viewInvoice($stockOutNo)
    {
        try {
            $orgId = auth()->user()->org_id ?? 1;
            
            // Get all items for this STOCK_OUT_NO
            $inventoryItems = $this->stockOutRepo->getStockOutByStockOutNo($stockOutNo);
            $firstItem = $inventoryItems->first();
                
            if ($inventoryItems->isEmpty()) {
                session()->flash('error', 'No items found for this Stock Out Number');
                return Redirect::back();
            }

            $priorViewCount = $this->stockOutRepo->countGatePassEvents($stockOutNo, 'view');
            $this->stockOutRepo->logGatePassEvent($stockOutNo, 'view', auth()->id(), (int) $orgId);
            $gatePassTopBanner = $priorViewCount >= 1 ? 'DUPLICATE COPY' : null;
            
            // Extract common data from first item
            $fromEmployeeId = $firstItem->from_employee_id ?? null;
            $toEmployeeId = $firstItem->to_employee_id ?? null;
            $fromBranchId = $firstItem->from_branch_id ?? (auth()->user()->branch_id ?? 1);
            $toBranchId = $firstItem->to_branch_id ?? null;
            
            // Generate gate pass code from first item's created_date
            $createdDate = $firstItem->created_date ?? now();
            $gatePassCode = date('YmdHis', strtotime($createdDate)) . '-' . $fromBranchId;
            
            // Collect primary device serials by set for conditional component serial display.
            $componentItemIds = [12, 14, 17, 41]; // HDD/SSD, RAM, Processor component IDs
            $primarySerialsBySet = [];
            foreach ($inventoryItems as $inv) {
                $setKey = (string)($inv->set_uniq_no ?? '');
                $serial = trim((string)($inv->uniq_serial_no ?? ''));
                if ($setKey === '' || $serial === '') {
                    continue;
                }
                if (in_array((int)$inv->item_id, $componentItemIds, true)) {
                    continue;
                }
                if (!isset($primarySerialsBySet[$setKey])) {
                    $primarySerialsBySet[$setKey] = [];
                }
                $primarySerialsBySet[$setKey][$serial] = true;
            }

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
                
                $rawSerialNo = trim((string)($inventoryItem->uniq_serial_no ?? ''));
                $setKey = (string)($inventoryItem->set_uniq_no ?? '');
                $isComponent = in_array((int)$inventoryItem->item_id, $componentItemIds, true);
                $serialNoForInvoice = $rawSerialNo;

                if ($isComponent) {
                    // If component serial matches primary device serial for this set, hide it.
                    $primarySetSerials = $primarySerialsBySet[$setKey] ?? [];
                    if ($rawSerialNo === '' || isset($primarySetSerials[$rawSerialNo])) {
                        $serialNoForInvoice = '';
                    }
                }

                $invoiceItems[] = [
                    'item' => $itemInfo,
                    'inventory_item' => $inventoryItem,
                    'quantity' => $inventoryItem->quantity ?? 1,
                    'description' => $inventoryItem->description ?? '',
                    'serial_no' => $serialNoForInvoice,
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
                    'condition' => $inventoryItem->condition ?? 'Good',
                    'receive_by' => '', // Not stored in inventory, can be added if needed
                    'mobile' => '', // Not stored in inventory, can be added if needed
                    'to_employee_id' => $inventoryItem->to_employee_id ?? '',
                    'to_branch_id' => $inventoryItem->to_branch_id ?? '',
                ];
            }
            // Get employee and branch information
            $fromEmployeeInfo = null;
            if (!empty($fromEmployeeId)) {
                $endPointUrl = "/api/v3/user-info-padmaportal?emp_id=" . $fromEmployeeId;
                $fromEmployeeInfo = json_decode($this->employeeInfo($endPointUrl));
            }
            $toEmployeeInfo = null;
            if (!empty($toEmployeeId)) {
                $endPointUrl = "/api/v3/user-info-padmaportal?emp_id=" . $toEmployeeId;
                $toEmployeeInfo = json_decode($this->employeeInfo($endPointUrl));
            }
            $fromBranchInfo = null;
            if (!empty($fromBranchId)) {
                $fromBranchInfo = DB::table('branch')->where('hr_branch_id', $fromBranchId)->first();
            }
            $toBranchInfo = null;
            if (!empty($toBranchId)) {
                $toBranchInfo = DB::table('branch')->where('hr_branch_id', $toBranchId)->first();
            }
            // Prepare invoice data
            $invoiceData = [
                'gate_pass_code' => $gatePassCode,
                'stock_out_no' => $stockOutNo,
                'gate_pass_top_banner' => $gatePassTopBanner,
                'global_note' => $firstItem->stock_out_note ?? '',
                'delivery_date' => date('Y-m-d', strtotime($createdDate)),
                'issue_date' => date('Y-m-d H:i:s', strtotime($createdDate)),
                'created_at' => $createdDate,
                'from_employee' => $fromEmployeeInfo,
                'to_employee' => $toEmployeeInfo,
                'from_branch' => $fromBranchInfo,
                'to_branch' => $toBranchInfo,
                'items' => $invoiceItems,
                'org_id' => $orgId,
            ];
            // Get employee and branch info for each item
            foreach ($invoiceData['items'] as &$item) {
                if (!empty($item['to_employee_id'])) {
                    $endPointUrl = "/api/v3/user-info-padmaportal?emp_id=" . $item['to_employee_id'];
                    $item['to_employee'] = json_decode($this->employeeInfo($endPointUrl));
                }
                if (!empty($item['to_branch_id'])) {
                    $item['to_branch'] = DB::table('branch')->where('id', $item['to_branch_id'])->first();
                }
            }
            
            return view('backend.item.stock_out_invoice', compact('invoiceData'));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return Redirect::back();
        }
    }

    /**
     * Log a browser print of the gate pass (afterprint).
     */
    public function logGatePassPrint(Request $request)
    {
        $stockOutNo = $request->input('stock_out_no');
        if (! is_string($stockOutNo) || trim($stockOutNo) === '') {
            return response()->json(['success' => false, 'message' => 'Invalid stock out number'], 422);
        }

        $orgId = auth()->user()->org_id ?? null;
        $this->stockOutRepo->logGatePassEvent(trim($stockOutNo), 'print', auth()->id(), $orgId !== null ? (int) $orgId : null);

        return response()->json(['success' => true]);
    }

    /**
     * Show receive stock page by stock out number
     */
    public function receiveStock(Request $request, $stockOutNo = null)
    {
        try {
            // Get stock out number or employee ID from request parameter or route parameter
            $stockOutNo = $request->input('stock_out_no') ?? $stockOutNo;
            $employeeId = $request->input('employee_id');
            
            $userAccessData = $this->userAccessFunction();
            
            // If no stock out number or employee ID provided, show empty form
            if (empty($stockOutNo) && empty($employeeId)) {
                return view('backend.item.receive_stock')
                    ->with('user_access', $userAccessData);
            }
            
            $orgId = auth()->user()->org_id ?? 1;
            $branchId = auth()->user()->branch_id ?? 1;
            
            // Get all items by stock out number or employee ID
            if (!empty($stockOutNo)) {
                $inventoryItems = $this->stockOutRepo->getStockOutByStockOutNo($stockOutNo);
            } elseif (!empty($employeeId)) {
                $inventoryItems = $this->stockOutRepo->getStockOutByEmployeeId($employeeId);
                // Show all items for the employee, not just first group
                if (!$inventoryItems || $inventoryItems->isEmpty()) {
                    $inventoryItems = collect();
                }
            } else {
                $inventoryItems = collect();
            }
            
            // Check if items are empty before accessing first()
            if ($inventoryItems->isEmpty()) {
                if (!empty($stockOutNo)) {
                    session()->flash('error', 'No items found for this Stock Out Number');
                } else {
                    session()->flash('error', 'No items found for this Employee ID');
                }
                return Redirect::back();
            }
            
            $firstItem = $inventoryItems->first();
            
            // If searching by employee ID, get the stock_out_no from first item
            if (empty($stockOutNo) && !empty($employeeId) && $firstItem) {
                $stockOutNo = $firstItem->stock_out_no ?? '';
            }
            
            // Extract common data from first item
            $fromEmployeeId = $firstItem->from_employee_id ?? null;
            $toEmployeeId = $firstItem->to_employee_id ?? null;
            $fromBranchId = $firstItem->from_branch_id ?? (auth()->user()->branch_id ?? 1);
            $toBranchId = $firstItem->to_branch_id ?? null;
            
            // Get employee and branch information
            $fromEmployeeInfo = null;
            if (!empty($fromEmployeeId)) {
                $endPointUrl = "/api/v3/user-info-padmaportal?emp_id=" . $fromEmployeeId;
                $fromEmployeeInfo = json_decode($this->employeeInfo($endPointUrl));
            }
            $toEmployeeInfo = null;
            if (!empty($toEmployeeId)) {
                $endPointUrl = "/api/v3/user-info-padmaportal?emp_id=" . $toEmployeeId;
                $toEmployeeInfo = json_decode($this->employeeInfo($endPointUrl));
            }
            $fromBranchInfo = null;
            if (!empty($fromBranchId)) {
                $fromBranchInfo = DB::table('branch')->where('hr_branch_id', $fromBranchId)->first();
            }
            $toBranchInfo = null;
            if (!empty($toBranchId)) {
                $toBranchInfo = DB::table('branch')->where('hr_branch_id', $toBranchId)->first();
            }
            
            // Collect invoice items with checkboxes
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
                
                // Check if already received
                $isReceived = ($inventoryItem->status == 3);
                
                $invoiceItems[] = [
                    'id' => $inventoryItem->id,
                    'item' => $itemInfo,
                    'inventory_item' => $inventoryItem,
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
                    'condition' => $inventoryItem->condition ?? 'Good',
                    'to_employee_id' => $inventoryItem->to_employee_id ?? '',
                    'to_branch_id' => $inventoryItem->to_branch_id ?? '',
                    'is_received' => $isReceived,
                ];
            }
            
            // Prepare invoice data
            $invoiceData = [
                'stock_out_no' => $stockOutNo,
                'created_at' => $firstItem->created_date ?? now(),
                'from_employee' => $fromEmployeeInfo,
                'to_employee' => $toEmployeeInfo,
                'from_branch' => $fromBranchInfo,
                'to_branch' => $toBranchInfo,
                'items' => $invoiceItems,
                'org_id' => $orgId,
            ];
            
            // Get employee and branch info for each item
            foreach ($invoiceData['items'] as &$item) {
                if (!empty($item['to_employee_id'])) {
                    $endPointUrl = "/api/v3/user-info-padmaportal?emp_id=" . $item['to_employee_id'];
                    $item['to_employee'] = json_decode($this->employeeInfo($endPointUrl));
                }
                if (!empty($item['to_branch_id'])) {
                    $item['to_branch'] = DB::table('branch')->where('hr_branch_id', $item['to_branch_id'])->first();
                }
            }
            
            return view('backend.item.receive_stock')
                ->with('invoiceData', $invoiceData)
                ->with('user_access', $userAccessData);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return Redirect::back();
        }
    }

    /**
     * Save receive stock
     */
    public function saveReceiveStock(Request $request)
    {
        $validation = $this->stockOutRepo->checkRequestValidityForReceive($request);
        if (!$validation['isValidationSuccess']) {
            session()->flash('error', $validation['error']);
            return Redirect::back();
        }

        DB::beginTransaction();
        try {
            $orgId = auth()->user()->org_id ?? 1;
            $branchId = auth()->user()->branch_id ?? 1;
            $stockOutNo = $request->input('stock_out_no');
            $inventoryIds = $request->input('inventory_ids', []);
            $remarks = $request->input('remarks', []);
            
            if (empty($inventoryIds)) {
                session()->flash('error', 'Please select at least one item to receive');
                return Redirect::back();
            }
            
            $updatedCount = $this->stockOutRepo->receiveStockOutItems($stockOutNo, $inventoryIds, $remarks);
            
            if ($updatedCount > 0) {
                DB::commit();
                session()->flash('success', "Successfully received {$updatedCount} item(s)!");
                return redirect()->route('receive-stock', $stockOutNo);
            } else {
                DB::rollBack();
                session()->flash('error', 'No items were received. Please check the selected items.');
                return Redirect::back();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
            return Redirect::back()->withInput();
        }
    }

    /**
     * Show transfer received items page
     */
    public function transferItems(Request $request)
    {
        try {
            $userAccessData = $this->userAccessFunction();
            $orgId = auth()->user()->org_id ?? 1;
            
            // Get employee ID from request
            $searchEmployeeId = $request->input('employee_id');
            $employeeId = $searchEmployeeId ?? null;
            
            // Get employee info if searching by employee ID
            $employeeInfo = null;
            $branchId = null;
            if (!empty($searchEmployeeId)) {
                $endPointUrl = "/api/v3/user-info-padmaportal?emp_id=" . $searchEmployeeId;
                $employeeInfo = json_decode($this->employeeInfo($endPointUrl));
                
                // Get branch_id from employee info if available
                if ($employeeInfo && isset($employeeInfo->branch_id)) {
                    $branch = DB::table('branch')
                        ->where('hr_branch_id', $employeeInfo->branch_id)
                        ->first();
                    $branchId = $branch->hr_branch_id ?? $employeeInfo->branch_id ?? null;
                }
            }
            
            // Get all received items for employee (branch is optional)
            $receivedItems = collect();
            if (!empty($employeeId)) {
                $receivedItems = $this->stockOutRepo->getReceivedItemsByEmployee($branchId, $employeeId);
            }
           
            // Get branch data for dropdown (for transfer form)
            $branchData = $this->branchRepo->withoutDeletingData();
            
            return view('backend.item.transfer_items')
                ->with('receivedItems', $receivedItems)
                ->with('branchData', $branchData)
                ->with('searchEmployeeId', $searchEmployeeId)
                ->with('employeeInfo', $employeeInfo)
                ->with('user_access', $userAccessData);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return Redirect::back();
        }
    }

    /**
     * Save transfer items
     */
    public function saveTransferItems(Request $request)
    {
        $validation = $this->stockOutRepo->checkRequestValidityForTransfer($request);
        if (!$validation['isValidationSuccess']) {
            session()->flash('error', $validation['error']);
            return Redirect::back();
        }

        DB::beginTransaction();
        try {
            $orgId = auth()->user()->org_id ?? 1;
            $fromBranchId = auth()->user()->branch_id ?? 1;
            $fromEmployeeId = auth()->user()->employee_id;
            $toEmployeeId = $request->input('to_employee_id');
            $toBranchId = $request->input('to_branch_id');
            $inventoryIds = $request->input('inventory_ids', []);
            $fromDeviceSerialNo = $request->input('from_device_serial_no');
            $toDeviceSerialNo = $request->input('to_device_serial_no');
            $remarks = trim((string) $request->input('remarks', ''));
            
            if (empty($inventoryIds)) {
                session()->flash('error', 'Please select at least one item to transfer');
                return Redirect::back();
            }
            
            $transferredCount = $this->stockOutRepo->transferItemsToEmployee(
                $inventoryIds, 
                $toEmployeeId, 
                $toBranchId, 
                $fromEmployeeId, 
                $fromBranchId, 
                $orgId,
                $fromDeviceSerialNo,
                $toDeviceSerialNo,
                $remarks
            );
            
            if ($transferredCount > 0) {
                DB::commit();
                session()->flash('success', "Successfully transferred {$transferredCount} item(s)!");
                return redirect()->route('transfer-items');
            } else {
                DB::rollBack();
                session()->flash('error', 'No items were transferred. Please check the selected items.');
                return Redirect::back();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
            return Redirect::back()->withInput();
        }
    }

    /**
     * Show return items to division page
     */
    public function returnToDivision(Request $request)
    {
        try {
            $userAccessData = $this->userAccessFunction();
            $orgId = auth()->user()->org_id ?? 1;
            
            // Get employee ID from request
            $searchEmployeeId = $request->input('employee_id');
            $employeeId = $searchEmployeeId ?? null;
            
            // Get employee info if searching by employee ID
            $employeeInfo = null;
            if (!empty($searchEmployeeId)) {
                $endPointUrl = "/api/v3/user-info-padmaportal?emp_id=" . $searchEmployeeId;
                $employeeInfo = json_decode($this->employeeInfo($endPointUrl));
            }
            
            // Get all items for return to division (received or transferred items)
            $itemsForReturn = collect();
            if (!empty($employeeId)) {
                $itemsForReturn = $this->stockOutRepo->getItemsForReturnToDivision($employeeId);
            }
           
            // Get division and branch data for dropdowns
            $divisionData = $this->divisionRepo->withoutDeletingData();
            $branchData   = $this->branchRepo->withoutDeletingData();
            
            return view('backend.item.return_to_division')
                ->with('itemsForReturn', $itemsForReturn)
                ->with('divisionData', $divisionData)
                ->with('branchData', $branchData)
                ->with('searchEmployeeId', $searchEmployeeId)
                ->with('employeeInfo', $employeeInfo)
                ->with('user_access', $userAccessData);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return Redirect::back();
        }
    }

    /**
     * Save return items to division
     */
    public function saveReturnToDivision(Request $request)
    {
        $validation = $this->stockOutRepo->checkRequestValidityForReturnToDivision($request);
        if (!$validation['isValidationSuccess']) {
            session()->flash('error', $validation['error']);
            return Redirect::back();
        }

        DB::beginTransaction();
        try {
            $orgId = auth()->user()->org_id ?? 1;
            $fromBranchId = auth()->user()->branch_id ?? 1;
            $fromEmployeeId = auth()->user()->employee_id;
            $divisionId        = $request->input('division_id');
            $toBranchId        = $request->input('branch_id');
            $condition         = $request->input('condition');
            $remarks           = $request->input('remarks');
            $inventoryIds      = $request->input('inventory_ids', []);
            $fromDeviceSerialNo = $request->input('from_device_serial_no');
            
            if (empty($inventoryIds)) {
                session()->flash('error', 'Please select at least one item to return');
                return Redirect::back();
            }
            
            $returnedCount = $this->stockOutRepo->returnItemsToDivision(
                $inventoryIds, 
                $divisionId, 
                $fromEmployeeId, 
                $fromBranchId, 
                $orgId,
                $fromDeviceSerialNo,
                $toBranchId,
                $condition,
                $remarks
            );
            
            if ($returnedCount > 0) {
                DB::commit();
                session()->flash('success', "Successfully returned {$returnedCount} item(s) to division!");
                return redirect()->route('return-to-division');
            } else {
                DB::rollBack();
                session()->flash('error', 'No items were returned. Please check the selected items.');
                return Redirect::back();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
            return Redirect::back()->withInput();
        }
    }


}

