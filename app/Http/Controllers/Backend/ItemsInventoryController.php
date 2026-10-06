<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\BrandRepo;
use App\Interfaces\Repo\Backend\CategoryRepo;
use App\Interfaces\Repo\Backend\ItemsInventoryRepo;
use App\Interfaces\Repo\Backend\ItemsRepo;
use App\Interfaces\Repo\Backend\UnitRepo;
use App\Interfaces\Repo\Backend\SupplierRepo;
use App\Support\SetUniqNoFormatter;
use App\Support\StockReferencePrefixBuilder;
use App\Support\WarrantyPeriodFormatter;
use App\Traits\ImageUploadTraits;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Milon\Barcode\DNS1D;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Routing\Controller as BaseController;

class ItemsInventoryController extends BaseController
{
    private $itemsInventoryRepo;
    private $suppRepo;
    private $brandRepo;
    private $optr;
    private $unitRepo;
    private $itemsRepo;
    use UserAccessTraits;
    use ImageUploadTraits;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct
    (
        ItemsInventoryRepo    $itemsInventoryRepo,
        SupplierRepo $suppRepo,
        ItemsRepo $itemsRepo,
        BrandRepo    $brandRepo,
        UnitRepo     $unitRepo,
        CategoryRepo $optr
    )
    {
        $this->middleware('auth');
        $this->itemsInventoryRepo = $itemsInventoryRepo;
        $this->suppRepo = $suppRepo;
        $this->itemsRepo = $itemsRepo;
        $this->brandRepo = $brandRepo;
        $this->optr = $optr;
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
        $orgId = auth()->user()->org_id;
//        $branchId = auth()->user()->branch_id;
        $userAccessData = $this->userAccessFunction();
        $supplierData = $this->suppRepo->withoutDeletingData();
        $brandData = $this->brandRepo->withoutDeletingData();
        $itemsData = $this->itemsRepo->withoutDeletingData();
        $catData = $this->optr->getCategory();
        $itemInventoryData = $this->itemsInventoryRepo->getItemInventoryInfo();
        
        $sizeData = $this->unitRepo->withoutDeletingData();
        return view('backend.item.item_inventory')->with('itemInventoryData', $itemInventoryData)
            ->with('supplierData', $supplierData)
            ->with('brandData', $brandData)
            ->with('catData', $catData)
            ->with('size', $sizeData)
            ->with('itemsData', $itemsData)
            ->with('user_access', $userAccessData);
    }

    public function list()
    {
        $userAccessData = $this->userAccessFunction();
        $itemInventoryData = $this->itemsInventoryRepo->getItemInventoryInfo();

        return view('backend.item.item_inventory_list')
            ->with('itemInventoryData', $itemInventoryData)
            ->with('user_access', $userAccessData);
    }

    public function saveUpdate(Request $request)
    {
        $validation = $this->itemsInventoryRepo->checkRequestValidity($request);
        if (!$validation['isValidationSuccess']) {
            session()->put('error', $validation['error']);
            return Redirect::back();
        }

        DB::beginTransaction();
        try {
            $orgId = auth()->user()->org_id;
            $branchId = auth()->user()->branch_id;
            $items = $request->input('item_id', []);
            $categories = $request->input('category_id', []);
            $itemSpecs = $request->input('item_specs', []);
            $invoiceAttachments = $request->file('invoice_attachment', []);

            // Highest numeric sequence for this org/branch (supports PBL-ABBR-#### and legacy codes).
            $lastCodeNumber = SetUniqNoFormatter::maxSequenceForOrgBranch((int) $orgId, $branchId);

            // For appended rows, generate different set_uniq_no per row.
            $generatedSetCodes = [];

            foreach ($items as $index => $itemId) {
                $categoryId = $categories[$index] ?? null;
                if (empty($itemId) || empty($categoryId)) {
                    continue;
                }

                if ($this->requiresComputerSpecs($itemId)) {
                    $specsJson = $itemSpecs[$index] ?? '';
                    if (!$this->hasRequiredComputerSpecs($specsJson)) {
                        $rowNumber = $index + 1;
                        throw new \Exception("Row {$rowNumber}: Desktop/Laptop/Server item requires RAM, Processor and HDD/SSD specifications.");
                    }
                }

                $invoiceAttachmentPath = null;
                if (isset($invoiceAttachments[$index]) && $invoiceAttachments[$index]) {
                    $invoiceAttachmentPath = $this->storeInvoiceAttachment($invoiceAttachments[$index]);
                }

                // Extract spec data from modal if exists
                $ramData = '';
                $storageData = '';
                $processorData = '';
                $conditionData = '';

                if (isset($itemSpecs[$index]) && !empty($itemSpecs[$index])) {
                    $specsData = json_decode($itemSpecs[$index], true);
                    if (is_array($specsData)) {
                        foreach ($specsData as $spec) {
                            if ($spec['type'] == 'RAM' && !empty($spec['value'])) {
                                $ramData = $spec['value'];
                                // Get condition from RAM spec if available
                                if (empty($conditionData) && !empty($spec['condition'])) {
                                    $conditionData = $spec['condition'];
                                }
                            } elseif ($spec['type'] == 'HDD' && !empty($spec['value'])) {
                                $storageData = $spec['value'];
                                // Get condition from HDD spec if available
                                if (empty($conditionData) && !empty($spec['condition'])) {
                                    $conditionData = $spec['condition'];
                                }
                            } elseif ($spec['type'] == 'Processor' && !empty($spec['value'])) {
                                $processorData = $spec['value'];
                                // Get condition from Processor spec if available
                                if (empty($conditionData) && !empty($spec['condition'])) {
                                    $conditionData = $spec['condition'];
                                }
                            }
                        }
                    }
                }

                // Get item image and title from items table
                $itemInfo = DB::table('item_info')->where('id', $itemId)->first();
                $itemImage = $itemInfo->image ?? '';
                $itemTitle = $itemInfo->item_title ?? '';
                $setAbbr = SetUniqNoFormatter::abbreviationFromItemTitle($itemTitle);

                do {
                    $lastCodeNumber++;
                    $uniqueCode = SetUniqNoFormatter::format($setAbbr, $lastCodeNumber);
                    $alreadyExists = DB::table('item_inventory')
                        ->where('org_id', $orgId)
                        ->where('set_uniq_no', $uniqueCode)
                        ->exists();
                } while ($alreadyExists || in_array($uniqueCode, $generatedSetCodes, true));
                $generatedSetCodes[] = $uniqueCode;

                $payload = [
                    'org_id' => $orgId,
                    'branch_id' => $branchId,
                    'item_id' => $itemId,
                    'category_id' => $categoryId,
                    'brand_id' => $request->input("brand_id.$index"),
                    'supplier_id' => $request->input("supplier_id.$index"),
                    'service_warranty' => WarrantyPeriodFormatter::formatYearsMonths(
                        $request->input("service_warranty_years.$index"),
                        $request->input("service_warranty_months.$index")
                    ),
                    'warranty' => WarrantyPeriodFormatter::formatYearsMonths(
                        $request->input("warranty_years.$index"),
                        $request->input("warranty_months.$index")
                    ),
                    'uniq_serial_no' => $request->input("serial_no.$index"),
                    'quantity' => $request->input("quantity.$index", 0),
                    'unit_size' => $request->input("unit_size.$index"),
                    'condition' => $conditionData ?: $request->input("condition.$index", ''),
                    'set_uniq_no' => $uniqueCode,
                    'stock_in_no' => StockReferencePrefixBuilder::formatStockInNumber($uniqueCode, (int) $branchId),
                    'price' => $request->input("price.$index", 0),
                    'vat' => $request->input("vat.$index", 0),
                    'tax' => $request->input("tax.$index", 0),
                    'description' => $request->input("description.$index", ''),
                    'ram' => $ramData ?: $request->input("ram.$index", ''),
                    'storage' => $storageData ?: $request->input("storage.$index", ''),
                    'processor' => $processorData ?: $request->input("processor.$index", ''),
                    'graphics' => $request->input("graphics.$index", ''),
                    'display' => $request->input("display.$index", ''),
                    'battery' => $request->input("battery.$index", ''),
                    'color' => $request->input("color.$index", ''),
                    'store_type' => 'SP',
                    'warehouse' => 'IT',
                    'remarks'=>'SI',
                    'created_date' => now(),
                    'created_by' => auth()->user()->id,
                    'store_employee_id' => auth()->user()->employee_id,
                    'image' => $itemImage,
                    'invoice_attachment' => $invoiceAttachmentPath,
                    'status' => 1,
                ];
//dd($payload);
                $this->itemsInventoryRepo->save($payload);

                // Handle item specs (RAM, Processor, HDD/SSD) - create separate inventory entries
                if (isset($itemSpecs[$index]) && !empty($itemSpecs[$index])) {
                    $this->processItemSpecs(
                        $itemSpecs[$index],
                        $orgId,
                        $branchId,
                        $categoryId,
                        $request,
                        $index,
                        $uniqueCode,
                        $invoiceAttachmentPath
                    );
                }
            }

            DB::commit();
            session()->put('eMessage', 'Inventory saved successfully.');
            return Redirect::route('items-inventory');
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            session()->put('error', $e->getMessage());
            return Redirect::back()->withInput();
        }
    }

    /**
     * Process item specifications and create separate inventory entries
     *
     * @param string $specsJson JSON string containing specs data
     * @param int $orgId Organization ID
     * @param int $branchId Branch ID
     * @param int $defaultCategoryId Default category ID
     * @param Request $request Request object
     * @param int $index Current row index
     * @param string $setUniqNo Set unique number to use for all specs in this group
     * @return void
     */
    private function processItemSpecs($specsJson, $orgId, $branchId, $defaultCategoryId, $request, $index, $setUniqNo, $invoiceAttachmentPath = null)
    {
        $specsData = json_decode($specsJson, true);

        if (!is_array($specsData)) {
            return;
        }

        foreach ($specsData as $spec) {
            $specItemId = $this->getSpecItemId($spec);

            if (!$specItemId) {
                continue;
            }

            $specCategoryId = $this->getSpecCategoryId($specItemId, $defaultCategoryId);
            $specPayload = $this->buildSpecPayload(
                $spec,
                $specItemId,
                $specCategoryId,
                $orgId,
                $branchId,
                $request,
                $index,
                $setUniqNo,
                $invoiceAttachmentPath
            );

            $this->itemsInventoryRepo->save($specPayload);
        }
    }

    /**
     * Determine item_id based on specification type
     *
     * @param array $spec Specification data
     * @return int|null Item ID or null if type not recognized
     */
    private function getSpecItemId($spec)
    {
        $type = strtoupper(trim((string)($spec['type'] ?? '')));
        $storageType = strtoupper(trim((string)($spec['storage_type'] ?? '')));

        if ($type === 'RAM') {
            return 17;
        }

        if ($type === 'PROCESSOR') {
            return 41;
        }

        if ($type === 'HDD' || $type === 'SSD' || $type === 'STORAGE') {
            if ($storageType === 'SSD') {
                return 14;
            }
            if ($storageType === 'HDD') {
                return 12;
            }
            return $this->getStorageItemId($spec['value'] ?? '');
        }

        return null;
    }

    private function requiresComputerSpecs($itemId): bool
    {
        return in_array((int)$itemId, [4, 5, 6], true);
    }

    private function hasRequiredComputerSpecs($specsJson): bool
    {
        $specsData = json_decode($specsJson, true);
        if (!is_array($specsData) || empty($specsData)) {
            return false;
        }

        $hasRam = false;
        $hasProcessor = false;
        $hasStorage = false;

        foreach ($specsData as $spec) {
            $type = strtoupper(trim((string)($spec['type'] ?? '')));
            $value = trim((string)($spec['value'] ?? ''));

            if ($value === '') {
                continue;
            }

            if ($type === 'RAM') {
                $hasRam = true;
            } elseif ($type === 'PROCESSOR') {
                $hasProcessor = true;
            } elseif ($type === 'HDD' || $type === 'SSD' || $type === 'STORAGE') {
                $hasStorage = true;
            }
        }

        return $hasRam && $hasProcessor && $hasStorage;
    }

    /**
     * Get storage item ID based on storage type (HDD or SSD)
     *
     * @param string $storageValue Storage value string
     * @return int Item ID (12 for HDD, 14 for SSD)
     */
    private function getStorageItemId($storageValue)
    {
        $storageValue = strtoupper($storageValue);

        // SSD takes priority (even if combined with HDD)
        if (strpos($storageValue, 'SSD') !== false) {
            return 14;
        }

        // Pure HDD
        if (strpos($storageValue, 'HDD') !== false) {
            return 12;
        }

        // Default to HDD if unclear
        return 12;
    }

    /**
     * Get category ID for specification item
     *
     * @param int $itemId Item ID
     * @param int $defaultCategoryId Default category ID to use if item not found
     * @return int Category ID
     */
    private function getSpecCategoryId($itemId, $defaultCategoryId)
    {
        $itemInfo = $this->itemsRepo->findById($itemId);
        //DB::table('items')->where('id', $itemId)->first();

        return $itemInfo->category_id ?? $defaultCategoryId;
    }

    /**
     * Build payload array for specification inventory entry
     *
     * @param array $spec Specification data
     * @param int $specItemId Specification item ID
     * @param int $specCategoryId Specification category ID
     * @param int $orgId Organization ID
     * @param int $branchId Branch ID
     * @param Request $request Request object
     * @param int $index Current row index
     * @param string $setUniqNo Set unique number to use for this spec
     * @return array Payload array for inventory entry
     */
    private function buildSpecPayload($spec, $specItemId, $specCategoryId, $orgId, $branchId, $request, $index, $setUniqNo, $invoiceAttachmentPath = null)
    {
        $specType = $spec['type'] ?? '';
        $specValue = $spec['value'] ?? '';

        // Use brand_id from spec data if available, otherwise fall back to main form brand_id
        $brandId = $spec['brand_id'] ?? $request->input("brand_id.$index");

        // Get serial_no from spec data, trim whitespace
        $serialNo = isset($spec['serial_no']) ? trim($spec['serial_no']) : '';

        // Get condition from spec data
        $condition = $spec['condition'] ?? '';

        // Use same supplier_id, warehouse, and store_type as main item
        $supplierId = $request->input("supplier_id.$index");

        // Get description from spec data, if not available use specValue
        $description = $spec['description'] ?? $specValue;
        
        return [
            'org_id' => $orgId,
            'branch_id' => $branchId,
            'item_id' => $specItemId,
            'category_id' => $specCategoryId,
            'brand_id' => $brandId,
            'supplier_id' => $supplierId,
            'service_warranty' => $spec['service_warranty'] ?? '',
            'warranty' => $spec['warranty'] ?? '',
            'uniq_serial_no' => $serialNo,
            'quantity' => $spec['quantity'] ?? 0,
            'unit_size' => $spec['unit_size'] ?? '',
            'condition' => $condition,
            'set_uniq_no' => $setUniqNo,
            'price' => $spec['price'] ?? 0,
            'vat' => $spec['vat'] ?? 0,
            'tax' => $spec['tax'] ?? 0,
            'description' => $description,
            'ram' => $specType === 'RAM' ? $specValue : '',
            'storage' => $specType === 'HDD' ? $specValue : '',
            'processor' => $specType === 'Processor' ? $specValue : '',
            'graphics' => '',
            'display' => '',
            'battery' => '',
            'color' => '',
            'store_type' => 'SP',
            'warehouse' => 'IT',
            'stock_in_no' => StockReferencePrefixBuilder::formatStockInNumber($setUniqNo, (int) $branchId),
            'created_date' => now(),
            'created_by' => auth()->user()->id,
            'store_employee_id' => auth()->user()->employee_id,
            'invoice_attachment' => $invoiceAttachmentPath,
            'status' => 1,
        ];
    }

    private function storeInvoiceAttachment($file): ?string
    {
        if (!$file) {
            return null;
        }

        $destinationPath = public_path('uploads/invoice-attachments');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $fileName = 'inv-' . now()->format('YmdHis') . '-' . Str::random(8) . '.' . $extension;
        $file->move($destinationPath, $fileName);

        return 'uploads/invoice-attachments/' . $fileName;
    }

    private function itemCodeGenerate($supplier_id, $brand_id, $category_id, $condition, $itemCode): string
    {
        $supplierName = $this->suppRepo->getSupplierInfoById($supplier_id);
        $brandName = $this->brandRepo->getBrandInfoById($brand_id);
        $categoryName = $this->optr->getCategoryInfoById($category_id);
        $lastCode = $this->itemsReceiveRepo->getLastCode();
        $code = (!empty($lastCode)) ? $lastCode['code'] : null;
        return (!empty($itemCode)) ? $itemCode : $this->generateRandomItemCode($supplierName['supplier_name'], $brandName['brand_name'], $categoryName['category'], $code);
    }

    public function edit(Request $request, $id)
    {
        $itemData = $this->itemsInventoryRepo->findById($id);
        if (!$itemData) {
            session()->put('error', 'Inventory item not found.');
            return Redirect::route('items-inventory-list');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($itemData);
        }

        $userAccessData = $this->userAccessFunction();
        $supplierData = $this->suppRepo->withoutDeletingData();
        $brandData = $this->brandRepo->withoutDeletingData();
        $itemsData = $this->itemsRepo->withoutDeletingData();
        $catData = $this->optr->getCategory();
        $sizeData = $this->unitRepo->withoutDeletingData();
        $showComputerSpecs = $this->requiresComputerSpecs((int)$itemData->item_id);
        $specifications = [];
        if ($showComputerSpecs) {
            $specifications = $this->getSetUniqNoSpecifications(
                (string)$itemData->set_uniq_no,
                $itemData->org_id,
                $itemData->branch_id,
                $itemData
            );
        }

        return view('backend.item.item_inventory_edit')
            ->with('itemData', $itemData)
            ->with('supplierData', $supplierData)
            ->with('brandData', $brandData)
            ->with('catData', $catData)
            ->with('size', $sizeData)
            ->with('itemsData', $itemsData)
            ->with('showComputerSpecs', $showComputerSpecs)
            ->with('specifications', $specifications)
            ->with('serviceWarrantyYm', WarrantyPeriodFormatter::parseYearsMonths((string) ($itemData->service_warranty ?? '')))
            ->with('warrantyYm', WarrantyPeriodFormatter::parseYearsMonths((string) ($itemData->warranty ?? '')))
            ->with('user_access', $userAccessData);
    }

    public function update(Request $request, $id)
    {
        $itemData = $this->itemsInventoryRepo->findById($id);
        if (!$itemData) {
            session()->put('error', 'Inventory item not found.');
            return Redirect::route('items-inventory-list');
        }

        $validated = $request->validate([
            'category_id' => 'required|integer',
            'item_id' => 'required|integer',
            'brand_id' => 'nullable|integer',
            'supplier_id' => 'nullable|integer',
            'service_warranty_years' => 'nullable|integer|min:0',
            'service_warranty_months' => 'nullable|integer|min:0',
            'warranty_years' => 'nullable|integer|min:0',
            'warranty_months' => 'nullable|integer|min:0',
            'serial_no' => 'nullable|string|max:255',
            'condition' => 'nullable|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit_size' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'vat' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'invoice_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'specifications' => 'nullable|array',
            'specifications.*.type' => 'required_with:specifications|string|in:RAM,Processor,SSD,HDD',
            'specifications.*.value' => 'required_with:specifications|string|max:255',
            'specifications.*.brand_id' => 'nullable|integer',
            'specifications.*.condition' => 'nullable|string|max:50',
        ]);

        $payload = [
            'item_id' => $validated['item_id'],
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'service_warranty' => WarrantyPeriodFormatter::formatYearsMonths(
                $request->input('service_warranty_years'),
                $request->input('service_warranty_months')
            ),
            'warranty' => WarrantyPeriodFormatter::formatYearsMonths(
                $request->input('warranty_years'),
                $request->input('warranty_months')
            ),
            'uniq_serial_no' => $validated['serial_no'] ?? '',
            'condition' => $validated['condition'] ?? '',
            'quantity' => $validated['quantity'],
            'unit_size' => $validated['unit_size'] ?? '',
            'price' => $validated['price'] ?? 0,
            'vat' => $validated['vat'] ?? 0,
            'tax' => $validated['tax'] ?? 0,
            'description' => $validated['description'] ?? '',
            'updated_by' => auth()->user()->id,
        ];

        if ($request->hasFile('invoice_attachment')) {
            $payload['invoice_attachment'] = $this->storeInvoiceAttachment($request->file('invoice_attachment'));
        }

        $this->itemsInventoryRepo->update($id, $payload);

        if ($this->requiresComputerSpecs((int)$validated['item_id'])) {
            $specs = $validated['specifications'] ?? [];
            $ramValue = '';
            $processorValue = '';
            $storageValue = '';
            foreach ($specs as $spec) {
                $type = trim((string)($spec['type'] ?? ''));
                $value = trim((string)($spec['value'] ?? ''));
                if ($type === 'RAM' && $value !== '') $ramValue = $value;
                if ($type === 'Processor' && $value !== '') $processorValue = $value;
                if (($type === 'SSD' || $type === 'HDD') && $value !== '') $storageValue = $value;
            }
            $this->itemsInventoryRepo->update($id, [
                'ram' => $ramValue,
                'processor' => $processorValue,
                'storage' => $storageValue,
            ]);

            $this->syncSetUniqNoSpecifications(
                (string)$itemData->set_uniq_no,
                $itemData->org_id,
                $itemData->branch_id,
                $specs,
                $payload,
                $itemData
            );
        } else {
            DB::table('item_inventory')
                ->where('set_uniq_no', (string)$itemData->set_uniq_no)
                ->where(function ($q) use ($itemData) {
                    if ($itemData->org_id !== null && $itemData->org_id !== '') {
                        $q->where('org_id', $itemData->org_id)
                            ->orWhereNull('org_id')
                            ->orWhere('org_id', '')
                            ->orWhere('org_id', 0);
                    }
                })
                ->where('branch_id', $itemData->branch_id)
                ->whereIn('item_id', [17, 41, 12, 14])
                ->update(['status' => 9]);
        }

        session()->put('eMessage', 'Inventory updated successfully.');

        return Redirect::route('items-inventory-list');
    }

    private function getSetUniqNoSpecifications(string $setUniqNo, $orgId, $branchId, $mainItem = null): array
    {
        if ($setUniqNo === '') {
            return [];
        }

        $query = DB::table('item_inventory')
            ->select(['id', 'item_id', 'ram', 'processor', 'storage', 'brand_id', 'condition', 'org_id', 'branch_id', 'status'])
            ->where('set_uniq_no', $setUniqNo)
            ->where('status', '<>', 9)
            ->whereIn('item_id', [17, 41, 12, 14])
            ->orderBy('id');

        // Prefer same branch rows when available
        if ($branchId !== null && $branchId !== '') {
            $query->where('branch_id', $branchId);
        }

        if ($orgId !== null && $orgId !== '') {
            $query->where(function ($q) use ($orgId) {
                $q->where('org_id', $orgId)
                    ->orWhereNull('org_id')
                    ->orWhere('org_id', '')
                    ->orWhere('org_id', 0);
            });
        }

        $specRows = $query->get();

        // Fallback: same set_uniq_no without strict branch/org filter
        if ($specRows->isEmpty()) {
            $specRows = DB::table('item_inventory')
                ->select(['id', 'item_id', 'ram', 'processor', 'storage', 'brand_id', 'condition', 'org_id', 'branch_id', 'status'])
                ->where('set_uniq_no', $setUniqNo)
                ->where('status', '<>', 9)
                ->whereIn('item_id', [17, 41, 12, 14])
                ->orderBy('id')
                ->get();
        }

        $specs = [];
        foreach ($specRows as $row) {
            if ((int)$row->item_id === 17 && !empty($row->ram)) {
                $specs[] = [
                    'type' => 'RAM',
                    'value' => trim((string)$row->ram),
                    'brand_id' => $row->brand_id,
                    'condition' => $row->condition,
                ];
            } elseif ((int)$row->item_id === 41 && !empty($row->processor)) {
                $specs[] = [
                    'type' => 'Processor',
                    'value' => trim((string)$row->processor),
                    'brand_id' => $row->brand_id,
                    'condition' => $row->condition,
                ];
            } elseif ((int)$row->item_id === 14 && !empty($row->storage)) {
                $specs[] = [
                    'type' => 'SSD',
                    'value' => trim((string)$row->storage),
                    'brand_id' => $row->brand_id,
                    'condition' => $row->condition,
                ];
            } elseif ((int)$row->item_id === 12 && !empty($row->storage)) {
                $specs[] = [
                    'type' => 'HDD',
                    'value' => trim((string)$row->storage),
                    'brand_id' => $row->brand_id,
                    'condition' => $row->condition,
                ];
            }
        }

        // Fallback from main Desktop/Laptop/Server row fields
        if (empty($specs) && $mainItem) {
            if (!empty($mainItem->ram)) {
                $specs[] = [
                    'type' => 'RAM',
                    'value' => trim((string)$mainItem->ram),
                    'brand_id' => $mainItem->brand_id ?? null,
                    'condition' => $mainItem->condition ?? null,
                ];
            }
            if (!empty($mainItem->processor)) {
                $specs[] = [
                    'type' => 'Processor',
                    'value' => trim((string)$mainItem->processor),
                    'brand_id' => $mainItem->brand_id ?? null,
                    'condition' => $mainItem->condition ?? null,
                ];
            }
            if (!empty($mainItem->storage)) {
                $storageValue = trim((string)$mainItem->storage);
                $specs[] = [
                    'type' => stripos($storageValue, 'SSD') !== false ? 'SSD' : 'HDD',
                    'value' => $storageValue,
                    'brand_id' => $mainItem->brand_id ?? null,
                    'condition' => $mainItem->condition ?? null,
                ];
            }
        }

        return $specs;
    }

    private function syncSetUniqNoSpecifications(
        string $setUniqNo,
        $orgId,
        $branchId,
        array $specifications,
        array $payload,
        $itemData
    ): void {
        $existingQuery = DB::table('item_inventory')
            ->where('set_uniq_no', $setUniqNo)
            ->where('status', '<>', 9)
            ->whereIn('item_id', [17, 41, 12, 14])
            ->orderBy('id');

        if ($branchId !== null && $branchId !== '') {
            $existingQuery->where('branch_id', $branchId);
        }
        if ($orgId !== null && $orgId !== '') {
            $existingQuery->where(function ($q) use ($orgId) {
                $q->where('org_id', $orgId)
                    ->orWhereNull('org_id')
                    ->orWhere('org_id', '')
                    ->orWhere('org_id', 0);
            });
        }

        $existingRows = $existingQuery->get();

        $existingByItemId = [];
        foreach ($existingRows as $row) {
            $existingByItemId[(int)$row->item_id][] = $row;
        }

        $usedIds = [];
        $keepStatus = (int)($itemData->status ?? 1);
        if ($keepStatus === 9) {
            $keepStatus = 1;
        }

        foreach ($specifications as $spec) {
            $type = trim((string)($spec['type'] ?? ''));
            $value = trim((string)($spec['value'] ?? ''));
            if ($type === '' || $value === '') {
                continue;
            }

            $itemId = match ($type) {
                'RAM' => 17,
                'Processor' => 41,
                'SSD' => 14,
                'HDD' => 12,
                default => null,
            };

            if (!$itemId) {
                continue;
            }

            $rowData = [
                'category_id' => $payload['category_id'],
                'brand_id' => $spec['brand_id'] ?? ($payload['brand_id'] ?? null),
                'supplier_id' => $payload['supplier_id'] ?? null,
                'service_warranty' => $payload['service_warranty'] ?? '',
                'warranty' => $payload['warranty'] ?? '',
                'condition' => $spec['condition'] ?? ($payload['condition'] ?? ''),
                'description' => $value,
                'ram' => $type === 'RAM' ? $value : '',
                'storage' => ($type === 'SSD' || $type === 'HDD') ? $value : '',
                'processor' => $type === 'Processor' ? $value : '',
                'status' => $keepStatus,
                'updated_at' => now(),
                'updated_by' => auth()->user()->id,
            ];

            if (!empty($existingByItemId[$itemId])) {
                $existingRow = array_shift($existingByItemId[$itemId]);
                $usedIds[] = (int)$existingRow->id;
                DB::table('item_inventory')
                    ->where('id', (int)$existingRow->id)
                    ->update($rowData);
                continue;
            }

            DB::table('item_inventory')->insert(array_merge([
                'org_id' => $orgId,
                'branch_id' => $branchId,
                'item_id' => $itemId,
                'uniq_serial_no' => '',
                'quantity' => 1,
                'unit_size' => '',
                'set_uniq_no' => $setUniqNo,
                'stock_in_no' => $itemData->stock_in_no,
                'price' => 0,
                'vat' => 0,
                'tax' => 0,
                'store_type' => 'SP',
                'warehouse' => $itemData->warehouse ?? 'IT',
                'remarks' => 'SI',
                'created_date' => now(),
                'created_by' => auth()->user()->id,
                'store_employee_id' => auth()->user()->employee_id,
                'invoice_attachment' => $itemData->invoice_attachment,
                'created_at' => now(),
                'updated_at' => now(),
            ], $rowData));
        }

        $allExistingIds = $existingRows->pluck('id')->map(fn($id) => (int)$id)->all();
        $toDeactivate = array_values(array_diff($allExistingIds, $usedIds));
        if (!empty($toDeactivate)) {
            DB::table('item_inventory')
                ->whereIn('id', $toDeactivate)
                ->update([
                    'status' => 9,
                    'updated_at' => now(),
                    'updated_by' => auth()->user()->id,
                ]);
        }
    }

    public function itemInfoById($id)
    {
        $itemData = $this->itemsInventoryRepo->findById($id);
        return json_encode($itemData);
    }


    public function brandWiseItemById($id)
    {
        $itemInfo = $this->itemsInventoryRepo->brandWiseItemById($id);
        return json_encode($itemInfo);
    }

    public function showBarcode()
    {
        return redirect()->route('barcode-generate');
    }
    public function barcodeGenerate(Request $request)
    {
        $userAccessData = $this->userAccessFunction();
        $item_info = $this->itemsInventoryRepo->itemAllInfoByOrgId(auth()->user()->org_id);
        $item_id = $request->input('item_id');
        $qty = $request->input('qty');
        $custom_price = $request->input('custom_price');
        $brand = $request->input('brand');
        $price = $request->input('price');
        $p_code = $request->input('p_code');
        $product_name = $request->input('product_name');
        $itemData = $this->itemsInventoryRepo->itemAInfoByItemId($item_id);
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
        $itemData = $this->itemsInventoryRepo->findBySupplierId($supplierId);
        return response()->json($itemData);
//        echo json_encode($itemData);exit;
    }

    public function getItemInfoByItemCode($code)
    {
        $itemData = $this->itemsInventoryRepo->findByItemCode($code);
        echo json_encode($itemData);
        exit;
    }

    public function active($id)
    {
        $this->itemsInventoryRepo->update($id, ['status' => 1]);
        return redirect()->route('items-inventory-list');
    }

    public function inActive($id)
    {
        $this->itemsInventoryRepo->update($id, ['status' => 0]);
        return redirect()->route('items-inventory-list');
    }

    public function delete($id)
    {
        $itemData = $this->itemsInventoryRepo->findById($id);
        if (!$itemData) {
            session()->put('error', 'Inventory item not found.');
            return redirect()->route('items-inventory-list');
        }

        $query = DB::table('item_inventory')
            ->where('org_id', (int)$itemData->org_id)
            ->where('branch_id', (int)$itemData->branch_id);

        if (!empty($itemData->stock_in_no)) {
            $query->where('stock_in_no', $itemData->stock_in_no);
        } elseif (!empty($itemData->set_uniq_no)) {
            $query->where('set_uniq_no', $itemData->set_uniq_no);
        } else {
            $query->where('id', (int)$itemData->id);
        }

        $query->update([
            'status' => 9,
            'updated_at' => now(),
            'updated_by' => auth()->user()->id,
        ]);

        session()->put('eMessage', 'Inventory deleted successfully.');
        return redirect()->route('items-inventory-list');
    }
}
