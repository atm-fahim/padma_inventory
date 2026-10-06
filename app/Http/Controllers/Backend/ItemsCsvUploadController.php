<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\BrandRepo;
use App\Interfaces\Repo\Backend\BranchRepo;
use App\Interfaces\Repo\Backend\CategoryRepo;
use App\Interfaces\Repo\Backend\ItemsCsvUploadRepo;
use App\Interfaces\Repo\Backend\SupplierRepo;
use App\Support\InventorySpreadsheetImportMapper;
use App\Support\SetUniqNoFormatter;
use App\Support\StockReferencePrefixBuilder;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ItemsCsvUploadController extends BaseController
{
    use UserAccessTraits;

    private $itemsCsvUploadRepo;
    private $brandRepo;
    private $supplierRepo;
    private $categoryRepo;
    private $branchRepo;

    public function __construct(
        ItemsCsvUploadRepo $itemsCsvUploadRepo,
        BrandRepo          $brandRepo,
        SupplierRepo       $supplierRepo,
        CategoryRepo       $categoryRepo,
        BranchRepo         $branchRepo
    )
    {
        $this->middleware('auth');
        $this->itemsCsvUploadRepo = $itemsCsvUploadRepo;
        $this->brandRepo = $brandRepo;
        $this->supplierRepo = $supplierRepo;
        $this->categoryRepo = $categoryRepo;
        $this->branchRepo = $branchRepo;
    }

    /**
     * Show CSV upload form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userAccessData = $this->userAccessFunction();
        $branchData = $this->branchRepo->withoutDeletingData();
        $defaultBranchId = auth()->user()->branch_id ?? null;

        return view('backend.items_csv_upload.upload')
            ->with('branchData', $branchData)
            ->with('defaultBranchId', $defaultBranchId)
            ->with('user_access', $userAccessData);
    }

    /**
     * Handle CSV file upload and show preview.
     */
    public function uploadCsv(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|integer',
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        try {
            $file = $request->file('csv_file');
            $csvData = [];
            $errors = [];
            $rowNumber = 0;

            $mapper = new InventorySpreadsheetImportMapper();
            $parsed = $mapper->parseFile($file->getRealPath());
            $rows = $parsed['rows'] ?? [];
            $importWarnings = $parsed['warnings'] ?? [];

            if (empty($rows)) {
                $message = 'No valid data found in file.';
                if (!empty($importWarnings)) {
                    $message .= ' ' . implode(' ', $importWarnings);
                }
                return Redirect::back()->with('eMessage', $message);
            }

            $orgId = auth()->user()->org_id ?? 1;

            // Get existing data for auto-population
            $brands = $this->brandRepo->getBrandsByOrgId($orgId);
            $suppliers = $this->supplierRepo->supplierInfoByOrgId($orgId);
            $categories = $this->categoryRepo->getCategoriesByOrgId($orgId);

            // Get all items for matching
            $items = DB::table('item_info')->where('status', 1)->get();

            foreach ($rows as $row) {
                $rowNumber++;

                $itemName = trim((string)($row['item_name'] ?? ''));
                $categoryName = trim((string)($row['category'] ?? ''));
                $brandName = trim((string)($row['brand'] ?? ''));
                $supplierName = trim((string)($row['supplier'] ?? ''));
                $price = trim((string)($row['price'] ?? '0'));
                $quantity = trim((string)($row['quantity'] ?? '0'));
                $unitSize = trim((string)($row['unit_size'] ?? ''));
                $serialNo = trim((string)($row['serial_no'] ?? ''));
                $condition = trim((string)($row['condition'] ?? ''));
                $warranty = trim((string)($row['warranty'] ?? ''));
                $serviceWarranty = trim((string)($row['service_warranty'] ?? ''));
                $vat = trim((string)($row['vat'] ?? '0'));
                $tax = trim((string)($row['tax'] ?? '0'));
                $description = trim((string)($row['description'] ?? ''));
                $ram = trim((string)($row['ram'] ?? ''));
                $storage = trim((string)($row['storage'] ?? ''));
                $processor = trim((string)($row['processor'] ?? ''));
                $graphics = trim((string)($row['graphics'] ?? ''));
                $display = trim((string)($row['display'] ?? ''));
                $battery = trim((string)($row['battery'] ?? ''));
                $color = trim((string)($row['color'] ?? ''));
                $setUniqNo = $this->normalizeProvidedSetUniqNo((string)($row['set_uniq_no'] ?? ''));

                if (empty($itemName) && empty($categoryName) && empty($price) && empty($quantity) && empty($setUniqNo)) {
                    continue;
                }

                if (empty($itemName)) {
                    $errors[] = "Row {$rowNumber}: Item name is required";
                    continue;
                }

                $mapperHelper = new InventorySpreadsheetImportMapper();

                // Find item id from ITEM_INFO by item_title (exact + fuzzy)
                $item = $items->first(function ($existingItem) use ($itemName) {
                    return Str::lower(trim((string)$existingItem->item_title)) === Str::lower($itemName);
                });
                if (!$item
                    && !$mapperHelper->isLikelyEmployeeId($itemName)
                    && !$mapperHelper->looksLikeColumnHeaderLabel($itemName)
                ) {
                    $normalizedTarget = Str::lower(preg_replace('/\s+/', ' ', $itemName) ?? $itemName);
                    $item = $items->first(function ($existingItem) use ($normalizedTarget) {
                        $title = Str::lower(trim((string)$existingItem->item_title));
                        return $title !== '' && (str_contains($normalizedTarget, $title) || str_contains($title, $normalizedTarget));
                    });
                }
                $itemId = $item ? $item->id : null;

                // Find or prepare brand
                $brand = $brands->first(function ($existingBrand) use ($brandName) {
                    return Str::lower(trim((string)$existingBrand->brand_name)) === Str::lower($brandName);
                });
                $brandId = $brand ? $brand->id : null;

                // Find or prepare supplier
                $supplier = $suppliers->first(function ($existingSupplier) use ($supplierName) {
                    return Str::lower(trim((string)$existingSupplier->supplier_name)) === Str::lower($supplierName);
                });
                $supplierId = $supplier ? $supplier->id : null;

                [$categoryId, $categoryName, $categoryMatched] = $this->resolveCategoryForImportRow(
                    $itemName,
                    $categoryName,
                    $item,
                    $categories
                );

                $csvData[] = [
                    'row_number' => $rowNumber,
                    'item_name' => $itemName,
                    'item_id' => $itemId,
                    'category_name' => $categoryName,
                    'category_id' => $categoryId,
                    'brand_name' => $brandName,
                    'brand_id' => $brandId,
                    'supplier_name' => $supplierName,
                    'supplier_id' => $supplierId,
                    'price' => $price,
                    'quantity' => $quantity,
                    'unit_size' => $unitSize,
                    'serial_no' => $serialNo,
                    'condition' => $condition,
                    'warranty' => $warranty,
                    'service_warranty' => $serviceWarranty,
                    'vat' => $vat,
                    'tax' => $tax,
                    'description' => $description,
                    'ram' => $ram,
                    'storage' => $storage,
                    'processor' => $processor,
                    'graphics' => $graphics,
                    'display' => $display,
                    'battery' => $battery,
                    'color' => $color,
                    'set_uniq_no' => $setUniqNo,
                    'auto_matched' => [
                        'item' => $itemId !== null,
                        'brand' => $brandId !== null,
                        'supplier' => $supplierId !== null,
                        'category' => $categoryMatched || $categoryId !== null,
                    ]
                ];
                }

            if (empty($csvData)) {
                return Redirect::back()->with('eMessage', 'No valid data found in CSV file.');
            }

            $branchId = $this->resolveUploadBranchId($request);
            $this->assignPreviewSetUniqNumbers($csvData, (int) $orgId, $branchId);

            // Store data in session for preview
            session(['csv_preview_data' => $csvData]);
            session(['csv_errors' => $errors]);
            session(['csv_upload_branch_id' => $branchId]);
            session(['csv_import_meta' => [
                'detected_format' => $parsed['detected_format'] ?? 'unknown',
                'header_row_index' => $parsed['header_row_index'] ?? null,
                'column_map' => $parsed['column_map'] ?? [],
                'warnings' => $importWarnings,
            ]]);

            $userAccessData = $this->userAccessFunction();
            $allBrands = $this->brandRepo->getBrandsByOrgId($orgId);
            $allSuppliers = $this->supplierRepo->supplierInfoByOrgId($orgId);
            $allCategories = $this->categoryRepo->getCategoriesByOrgId($orgId);

            $selectedBranch = DB::table('branch')
                ->where(function ($q) use ($branchId) {
                    $q->where('hr_branch_id', $branchId)->orWhere('id', $branchId);
                })
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '<>', 9);
                })
                ->first();

            return view('backend.items_csv_upload.preview')
                ->with('csvData', $csvData)
                ->with('selectedBranchId', $branchId)
                ->with('selectedBranch', $selectedBranch)
                ->with('importMeta', session('csv_import_meta'))
                ->with('errors', $errors)
                ->with('brands', $allBrands)
                ->with('suppliers', $allSuppliers)
                ->with('categories', $allCategories)
                ->with('items', $items)
                ->with('user_access', $userAccessData);

        } catch (\Exception $e) {
            return Redirect::back()->with('eMessage', 'Error processing CSV: ' . $e->getMessage());
        }
    }

    /**
     * Download sample file for CSV/XLSX upload format.
     */
    public function downloadSample($format = 'csv')
    {
        $format = strtolower((string)$format);
        $headers = $this->getSampleHeaders();
        $rows = $this->getSampleRows();

        if ($format === 'xlsx' || $format === 'xls') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray($headers, null, 'A1');
            $sheet->fromArray($rows, null, 'A2');

            $writerType = $format === 'xls' ? 'Xls' : 'Xlsx';
            $fileExtension = $format === 'xls' ? 'xls' : 'xlsx';
            $fileName = "item_inventory_sample.{$fileExtension}";

            return new StreamedResponse(function () use ($spreadsheet, $writerType) {
                $writer = IOFactory::createWriter($spreadsheet, $writerType);
                $writer->save('php://output');
            }, 200, [
                'Content-Type' => $format === 'xls'
                    ? 'application/vnd.ms-excel'
                    : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
                'Pragma' => 'public',
                'Expires' => '0',
            ]);
        }

        $fileName = 'item_inventory_sample.csv';
        return new StreamedResponse(function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($output, $headers);
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
            'Expires' => '0',
        ]);
    }

    /**
     * Store CSV data to database.
     */
    public function storeCsv(Request $request)
    {
        $csvData = session('csv_preview_data');

        if (!$csvData) {
            return Redirect::back()->with('eMessage', 'No CSV data found in session. Please upload again.');
        }

        $orgId = auth()->user()->org_id ?? 1;
        $branchId = $this->resolveUploadBranchId($request);

        if ($branchId === null || $branchId === '') {
            return Redirect::back()->with('eMessage', 'Please select a branch before importing.');
        }

        DB::beginTransaction();
        try {
            $successCount = 0;
            $updateCount = 0;
            $skipCount = 0;
            $errors = [];

            // Max sequence from existing codes (PBL-ABBR-#### and legacy shapes).
            $lastCodeNumber = $this->resolveMaxInventorySequenceNumber($orgId, $branchId);

            $generatedSetCodesInRequest = [];
            $exportRows = [];
            $orgCategories = $this->categoryRepo->getCategoriesByOrgId($orgId);
            $defaultImportCategoryName = (new InventorySpreadsheetImportMapper())->defaultBranchImportCategory();
            $defaultImportCategory = $this->findCategoryByName($orgCategories, $defaultImportCategoryName);

            foreach ($csvData as $index => $row) {
                // Get overridden values from form if any
                $itemId = $request->input("item_id.{$index}", $row['item_id']);
                $categoryId = $request->input("category_id.{$index}", $row['category_id']);
                $brandId = $request->input("brand_id.{$index}", $row['brand_id']);
                $supplierId = $request->input("supplier_id.{$index}", $row['supplier_id']);
                $providedSetUniqNo = $this->normalizeProvidedSetUniqNo(
                    (string) $request->input("set_uniq_no.{$index}", $row['set_uniq_no'] ?? '')
                );

                $row['item_name'] = trim((string) $request->input("item_name.{$index}", $row['item_name'] ?? ''));
                $row['category_name'] = trim((string) $request->input("category_name.{$index}", $row['category_name'] ?? ''));

                if (empty($categoryId) && $defaultImportCategory) {
                    $categoryId = (int) $defaultImportCategory->id;
                    $row['category_name'] = (string) ($defaultImportCategory->category_title ?? $defaultImportCategory->category ?? $defaultImportCategoryName);
                } elseif (empty($categoryId) && $row['category_name'] === '') {
                    $row['category_name'] = $defaultImportCategoryName;
                }
                $row['brand_name'] = trim((string) $request->input("brand_name.{$index}", $row['brand_name'] ?? ''));
                $row['price'] = $request->input("price.{$index}", $row['price'] ?? 0);
                $row['quantity'] = $request->input("quantity.{$index}", $row['quantity'] ?? 1);
                $row['unit_size'] = trim((string) $request->input("unit_size.{$index}", $row['unit_size'] ?? ''));
                $row['serial_no'] = trim((string) $request->input("serial_no.{$index}", $row['serial_no'] ?? ''));
                $row['condition'] = trim((string) $request->input("condition.{$index}", $row['condition'] ?? ''));
                $row['description'] = trim((string) $request->input("description.{$index}", $row['description'] ?? ''));
                $row['ram'] = trim((string) $request->input("ram.{$index}", $row['ram'] ?? ''));
                $row['storage'] = trim((string) $request->input("storage.{$index}", $row['storage'] ?? ''));
                $row['processor'] = trim((string) $request->input("processor.{$index}", $row['processor'] ?? ''));

                // Create new item if name provided but ID is null
                if (!$itemId && !empty($row['item_name'])) {
                    $newItem = DB::table('item_info')->insertGetId([
                        'org_id' => $orgId,
                        'branch_id' => $branchId,
                        'item_title' => $row['item_name'],
                        'category_id' => $categoryId,
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $itemId = $newItem;
                }

                // Create new brand if name provided but ID is null
                if (!$brandId && !empty($row['brand_name'])) {
                    $newBrand = $this->brandRepo->save([
                        'org_id' => $orgId,
                        'brand_name' => $row['brand_name'],
                        'status' => 1,
                    ]);
                    $brandId = $newBrand->id;
                }

                // Create new supplier if name provided but ID is null
                if (!$supplierId && !empty($row['supplier_name'])) {
                    $newSupplier = $this->supplierRepo->save([
                        'org_id' => $orgId,
                        'supplier_name' => $row['supplier_name'],
                        'status' => 1,
                    ]);
                    $supplierId = $newSupplier->id;
                }

                // Create new category if name provided but ID is null
                if (!$categoryId && !empty($row['category_name'])) {
                    $newCategory = $this->categoryRepo->save([
                        'org_id' => $orgId,
                        'category_title' => $row['category_name'],
                        'status' => 1,
                    ]);
                    $categoryId = $newCategory->id;
                }

                if (!$itemId || !$categoryId) {
                    $errors[] = "Row {$row['row_number']}: Missing item or category";
                    continue;
                }

                // If SET_UNIQ_NO exists in sheet and found in DB, update that existing record.
                $existingInventoryRow = null;
                if (!empty($providedSetUniqNo)) {
                    $existingInventoryRow = DB::table('item_inventory')
                        ->where('org_id', $orgId)
                        ->where('branch_id', $branchId)
                        ->where('set_uniq_no', $providedSetUniqNo)
                        ->when($itemId, function ($query) use ($itemId) {
                            return $query->where('item_id', $itemId);
                        })
                        ->orderByDesc('id')
                        ->first();
                }

                $uniqueCode = $this->resolveSetUniqNoForImport(
                    $providedSetUniqNo,
                    $orgId,
                    $branchId,
                    (int) $itemId,
                    $existingInventoryRow,
                    $lastCodeNumber,
                    $generatedSetCodesInRequest,
                    (string) ($row['item_name'] ?? '')
                );

                // Get item image from items table
                $itemInfo = DB::table('item_info')->where('id', $itemId)->first();
                $itemImage = $itemInfo->image ?? '';

                // Save main item inventory
                $itemData = [
                    'org_id' => $orgId,
                    'branch_id' => $branchId,
                    'item_id' => $itemId,
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'supplier_id' => $supplierId,
                    'service_warranty' => $row['service_warranty'] ?? '',
                    'warranty' => $row['warranty'] ?? '',
                    'uniq_serial_no' => $row['serial_no'] ?? '',
                    'quantity' => $row['quantity'] ?? 0,
                    'unit_size' => $row['unit_size'] ?? '',
                    'condition' => $row['condition'] ?? '',
                    'set_uniq_no' => $uniqueCode,
                    'stock_in_no' => StockReferencePrefixBuilder::formatStockInNumber($uniqueCode, $branchId !== null && $branchId !== '' ? (int) $branchId : null),
                    'price' => $row['price'] ?? 0,
                    'vat' => $row['vat'] ?? 0,
                    'tax' => $row['tax'] ?? 0,
                    'description' => $row['description'] ?? '',
                    'ram' => $row['ram'] ?? '',
                    'storage' => $row['storage'] ?? '',
                    'processor' => $row['processor'] ?? '',
                    'graphics' => $row['graphics'] ?? '',
                    'display' => $row['display'] ?? '',
                    'battery' => $row['battery'] ?? '',
                    'color' => $row['color'] ?? '',
                    'store_type' => 'SP',
                    'warehouse' => 'IT',
                    'remarks' => 'SI',
                    'created_date' => now(),
                    'created_by' => auth()->user()->id,
                    'store_employee_id' => auth()->user()->employee_id ?? '',
                    'image' => $itemImage,
                    'status' => 1,
                ];

                if ($existingInventoryRow) {
                    DB::table('item_inventory')
                        ->where('id', $existingInventoryRow->id)
                        ->update($itemData);
                    $updateCount++;
                    $exportRows[] = $this->buildCsvExportRow($row, $uniqueCode, $itemData, 'Updated');
                    continue;
                }

                $this->itemsCsvUploadRepo->save($itemData);

                // Process specifications as separate inventory entries (for Desktop/Laptop/Server packages)
                // Create separate entries for RAM, Storage, Processor with same set_uniq_no
                $this->processSpecsFromCsv(
                    $row,
                    $orgId,
                    $branchId,
                    $categoryId,
                    $brandId,
                    $supplierId,
                    $uniqueCode
                );

                $successCount++;
                $exportRows[] = $this->buildCsvExportRow($row, $uniqueCode, $itemData, 'Imported');
            }

            DB::commit();

            // Clear session data
            session()->forget('csv_preview_data');
            session()->forget('csv_errors');
            session()->forget('csv_import_meta');
            session()->forget('csv_upload_branch_id');
            session(['csv_upload_export_rows' => $exportRows]);

            $message = "CSV upload completed! Successfully imported {$successCount} items to inventory.";
            if ($updateCount > 0) {
                $message .= " Updated {$updateCount} existing items by SET_UNIQ_NO.";
            }
            if ($skipCount > 0) {
                $message .= " Skipped {$skipCount} items.";
            }
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " errors occurred.";
            }

            return Redirect::route('items-inventory-csv-upload')
                ->with('success', $message)
                ->with('auto_download_csv_result', true);

        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with('eMessage', 'Error saving data: ' . $e->getMessage());
        }
    }

    /**
     * Get or create set_uniq_no based on combination of item_id, category_id, ram, storage, processor
     */
    private function getOrCreateSetUniqNo($orgId, $branchId, $itemId, $categoryId, $ram, $storage, $processor, $condition, &$lastCodeNumber)
    {
        // Normalize the values for comparison
        $ram = trim($ram ?? '');
        $storage = trim($storage ?? '');
        $processor = trim($processor ?? '');
        $condition = trim($condition ?? '');

        // Check if a set_uniq_no already exists for this combination
        $existingSetUniqNo = DB::table('item_inventory')
            ->where('org_id', $orgId)
            ->where('branch_id', $branchId)
            ->where('item_id', $itemId)
            ->where('category_id', $categoryId)
            ->where('ram', $ram)
            ->where('storage', $storage)
            ->where('processor', $processor)
            ->where('condition', $condition)
            ->where('status', 1)
            ->value('set_uniq_no');

        // If found, return the existing set_uniq_no
        if (!empty($existingSetUniqNo)) {
            return $existingSetUniqNo;
        }

        // Otherwise, generate a new one
        $itemTitle = (string) DB::table('item_info')->where('id', $itemId)->value('item_title');
        $lastCodeNumber++;

        return SetUniqNoFormatter::format(
            SetUniqNoFormatter::abbreviationFromItemTitle($itemTitle),
            $lastCodeNumber
        );
    }

    /**
     * Read CSV/XLSX/XLS rows and normalize header keys.
     *
     * @param string $filePath
     * @return array{0: array, 1: array}
     */
    private function readSpreadsheetRows($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray('', false, false, false);
        if (empty($rows)) {
            return [[], []];
        }

        $rawHeader = array_shift($rows);
        $header = array_map(function ($column) {
            return Str::of((string)$column)
                ->trim()
                ->lower()
                ->replace('-', '_')
                ->replace(' ', '_')
                ->toString();
        }, $rawHeader);

        $normalizedRows = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $normalizedRows[] = array_map(function ($value) {
                return trim((string)$value);
            }, $row);
        }

        return [$header, $normalizedRows];
    }

    /**
     * Generate next unique SET_UNIQ_NO for inventory import.
     *
     * @param array<int, string>|null $generatedSetCodesInRequest When set, avoid duplicates within the same CSV save.
     */
    private function generateNextSetUniqNo($orgId, $branchId, $categoryId, &$lastCodeNumber, string $itemTitle, ?array &$generatedSetCodesInRequest = null)
    {
        do {
            $lastCodeNumber++;
            $uniqueCode = SetUniqNoFormatter::format(
                SetUniqNoFormatter::abbreviationFromItemTitle($itemTitle),
                $lastCodeNumber
            );
            $alreadyExists = $this->setUniqNoConflictsWithExisting(
                $uniqueCode,
                (int) $orgId,
                $branchId,
                null
            );
            $dupInBatch = $generatedSetCodesInRequest !== null
                && in_array($uniqueCode, $generatedSetCodesInRequest, true);
        } while ($alreadyExists || $dupInBatch);

        return $uniqueCode;
    }

    /**
     * Generate SET_UNIQ_NO using a shared batch sequence (e.g. PBL-LP-5001) from item name.
     */
    private function generateBatchSetUniqNo($orgId, $branchId, $categoryId, $batchCodeNumber, &$lastCodeNumber, &$generatedSetCodesInRequest, string $itemTitle)
    {
        $candidateCode = SetUniqNoFormatter::format(
            SetUniqNoFormatter::abbreviationFromItemTitle($itemTitle),
            $batchCodeNumber
        );
        $existsInDb = DB::table('item_inventory')
            ->where('org_id', $orgId)
            ->where('branch_id', $branchId)
            ->where('set_uniq_no', $candidateCode)
            ->exists();

        if (!$existsInDb && !in_array($candidateCode, $generatedSetCodesInRequest, true)) {
            $generatedSetCodesInRequest[] = $candidateCode;
            return $candidateCode;
        }

        // Fallback: if candidate already exists (same item type repeated or historical collision),
        // move to next available sequence.
        $nextCode = $this->generateNextSetUniqNo($orgId, $branchId, $categoryId, $lastCodeNumber, $itemTitle, $generatedSetCodesInRequest);
        $generatedSetCodesInRequest[] = $nextCode;
        return $nextCode;
    }

    private function getSampleHeaders()
    {
        return [
            'set_uniq_no',
            'item_name',
            'category',
            'brand',
            'supplier',
            'price',
            'quantity',
            'unit_size',
            'serial_no',
            'condition',
            'warranty',
            'service_warranty',
            'vat',
            'tax',
            'description',
            'ram',
            'storage',
            'processor',
            'graphics',
            'display',
            'battery',
            'color',
        ];
    }

    private function getSampleRows()
    {
        return [
            ['', 'Dell Laptop', 'Laptop', 'Dell', 'Tech Supplier', '55000', '1', 'PCS', 'DL123456', 'New', '1 Year', '1 Year', '0', '0', 'Dell Latitude 5420', '8GB', '512GB SSD', 'Intel Core i5', 'Intel UHD', '14 inch', '40Wh', 'Black'],
            ['', 'HP Desktop', 'Desktop', 'HP', 'Tech Supplier', '45000', '1', 'PCS', 'HP998877', 'New', '1 Year', '1 Year', '0', '0', 'HP ProDesk 400', '16GB', '1TB HDD', 'Intel Core i7', 'Intel UHD', '', '', 'Black'],
            ['', 'Lenovo Server', 'Server', 'Lenovo', 'Tech Supplier', '85000', '1', 'PCS', 'LN445566', 'New', '3 Years', '3 Years', '0', '0', 'Lenovo ThinkServer', '32GB', '2TB HDD', 'Intel Xeon', '', '', '', 'Silver'],
            ['PBL-MN-5001', 'HP Monitor', 'Monitor', 'HP', 'Tech Supplier', '12000', '1', 'PCS', 'HP789012', 'New', '2 Years', '1 Year', '0', '0', 'HP 24 inch LED Monitor', '', '', '', '', '24 inch', '', 'Black'],
            ['', 'Keyboard', 'Accessories', 'Logitech', 'Tech Supplier', '1500', '1', 'PCS', 'LK555123', 'New', '1 Year', '', '0', '0', 'Logitech Wireless Keyboard', '', '', '', '', '', '', 'Black'],
            ['', 'Mouse', 'Accessories', 'Logitech', 'Tech Supplier', '800', '1', 'PCS', 'LM445566', 'New', '1 Year', '', '0', '0', 'Logitech Wireless Mouse', '', '', '', '', '', '', 'Black'],
        ];
    }

    /**
     * Highest numeric sequence already used in set_uniq_no for this org/branch
     * (supports PBL-ABBR-####, legacy PBL-####CAT#, BR{branch}-####-...).
     */
    private function resolveMaxInventorySequenceNumber(int $orgId, $branchId): int
    {
        return SetUniqNoFormatter::maxSequenceForOrgBranch($orgId, $branchId);
    }

    /**
     * Process specifications from CSV row and create separate inventory entries
     * for RAM, Processor, and Storage (similar to manual entry)
     *
     * @param array $row CSV row data
     * @param int $orgId Organization ID
     * @param int $branchId Branch ID
     * @param int $categoryId Main item category ID
     * @param int|null $brandId Brand ID
     * @param int|null $supplierId Supplier ID
     * @param string $setUniqNo Set unique number for this package
     * @return void
     */
    private function processSpecsFromCsv($row, $orgId, $branchId, $categoryId, $brandId, $supplierId, $setUniqNo)
    {
        $ram = trim($row['ram'] ?? '');
        $storage = trim($row['storage'] ?? '');
        $processor = trim($row['processor'] ?? '');
        $condition = trim($row['condition'] ?? '');

        // Create RAM entry if RAM specification is provided
        if (!empty($ram)) {
            $ramItemId = 17; // RAM item ID
            $ramCategoryId = $this->getSpecCategoryId($ramItemId, $categoryId);

            $ramData = [
                'org_id' => $orgId,
                'branch_id' => $branchId,
                'item_id' => $ramItemId,
                'category_id' => $ramCategoryId,
                'brand_id' => $brandId,
                'supplier_id' => $supplierId,
                'service_warranty' => $row['service_warranty'] ?? '',
                'warranty' => $row['warranty'] ?? '',
                'uniq_serial_no' => '',
                'quantity' => 1,
                'unit_size' => $row['unit_size'] ?? '',
                'condition' => $condition,
                'set_uniq_no' => $setUniqNo,
                'stock_in_no' => StockReferencePrefixBuilder::formatStockInNumber($setUniqNo, $branchId !== null && $branchId !== '' ? (int) $branchId : null),
                'price' => 0,
                'vat' => 0,
                'tax' => 0,
                'description' => $ram,
                'ram' => $ram,
                'storage' => '',
                'processor' => '',
                'graphics' => '',
                'display' => '',
                'battery' => '',
                'color' => '',
                'store_type' => 'SP',
                'warehouse' => 'IT',
                'remarks' => 'SI',
                'created_date' => now(),
                'created_by' => auth()->user()->id,
                'store_employee_id' => auth()->user()->employee_id ?? '',
                'status' => 1,
            ];

            $this->itemsCsvUploadRepo->save($ramData);
        }

        // Create Storage entry if storage specification is provided
        if (!empty($storage)) {
            $storageItemId = $this->getStorageItemId($storage);
            $storageCategoryId = $this->getSpecCategoryId($storageItemId, $categoryId);

            $storageData = [
                'org_id' => $orgId,
                'branch_id' => $branchId,
                'item_id' => $storageItemId,
                'category_id' => $storageCategoryId,
                'brand_id' => $brandId,
                'supplier_id' => $supplierId,
                'service_warranty' => $row['service_warranty'] ?? '',
                'warranty' => $row['warranty'] ?? '',
                'uniq_serial_no' => '',
                'quantity' => 1,
                'unit_size' => $row['unit_size'] ?? '',
                'condition' => $condition,
                'set_uniq_no' => $setUniqNo,
                'stock_in_no' => StockReferencePrefixBuilder::formatStockInNumber($setUniqNo, $branchId !== null && $branchId !== '' ? (int) $branchId : null),
                'price' => 0,
                'vat' => 0,
                'tax' => 0,
                'description' => $storage,
                'ram' => '',
                'storage' => $storage,
                'processor' => '',
                'graphics' => '',
                'display' => '',
                'battery' => '',
                'color' => '',
                'store_type' => 'SP',
                'warehouse' => 'IT',
                'remarks' => 'SI',
                'created_date' => now(),
                'created_by' => auth()->user()->id,
                'store_employee_id' => auth()->user()->employee_id ?? '',
                'status' => 1,
            ];

            $this->itemsCsvUploadRepo->save($storageData);
        }

        // Create Processor entry if processor specification is provided
        if (!empty($processor)) {
            $processorItemId = 41; // Processor item ID
            $processorCategoryId = $this->getSpecCategoryId($processorItemId, $categoryId);

            $processorData = [
                'org_id' => $orgId,
                'branch_id' => $branchId,
                'item_id' => $processorItemId,
                'category_id' => $processorCategoryId,
                'brand_id' => $brandId,
                'supplier_id' => $supplierId,
                'service_warranty' => $row['service_warranty'] ?? '',
                'warranty' => $row['warranty'] ?? '',
                'uniq_serial_no' => '',
                'quantity' => 1,
                'unit_size' => $row['unit_size'] ?? '',
                'condition' => $condition,
                'set_uniq_no' => $setUniqNo,
                'stock_in_no' => StockReferencePrefixBuilder::formatStockInNumber($setUniqNo, $branchId !== null && $branchId !== '' ? (int) $branchId : null),
                'price' => 0,
                'vat' => 0,
                'tax' => 0,
                'description' => $processor,
                'ram' => '',
                'storage' => '',
                'processor' => $processor,
                'graphics' => '',
                'display' => '',
                'battery' => '',
                'color' => '',
                'store_type' => 'SP',
                'warehouse' => 'IT',
                'remarks' => 'SI',
                'created_date' => now(),
                'created_by' => auth()->user()->id,
                'store_employee_id' => auth()->user()->employee_id ?? '',
                'status' => 1,
            ];

            $this->itemsCsvUploadRepo->save($processorData);
        }
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
            return 14; // SSD item ID
        }

        // Pure HDD
        if (strpos($storageValue, 'HDD') !== false) {
            return 12; // HDD item ID
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
        $itemInfo = DB::table('item_info')->where('id', $itemId)->first();
        return $itemInfo->category_id ?? $defaultCategoryId;
    }

    private function resolveUploadBranchId(Request $request)
    {
        $branchId = $request->input('branch_id');
        if ($branchId === null || $branchId === '') {
            $branchId = session('csv_upload_branch_id');
        }
        if ($branchId === null || $branchId === '') {
            $branchId = auth()->user()->branch_id ?? null;
        }

        return $branchId !== null && $branchId !== '' ? (int) $branchId : null;
    }

    private function resolveCategoryForImportRow(string $itemName, string $categoryNameFromFile, $item, $categories): array
    {
        unset($itemName, $categoryNameFromFile, $item);

        $categoryName = (new InventorySpreadsheetImportMapper())->defaultBranchImportCategory();
        $matchedCategory = $this->findCategoryByName($categories, $categoryName);

        if ($matchedCategory) {
            return [
                (int) $matchedCategory->id,
                (string) ($matchedCategory->category_title ?? $matchedCategory->category ?? $categoryName),
                true,
            ];
        }

        return [null, $categoryName, false];
    }

    private function sanitizeCategoryLabel(string $itemName, string $categoryName): string
    {
        $mapper = new InventorySpreadsheetImportMapper();
        $categoryName = trim($categoryName);
        $itemName = trim($itemName);

        if ($categoryName === '') {
            return '';
        }

        if ($mapper->isLikelyEmployeeId($categoryName) || $mapper->looksLikeColumnHeaderLabel($categoryName)) {
            return '';
        }

        if ($itemName !== '' && Str::lower($categoryName) === Str::lower($itemName)) {
            return '';
        }

        $lower = Str::lower($categoryName);
        if (str_contains($lower, 'branch') || str_contains($lower, 'division') || str_contains($lower, 'employee')) {
            return '';
        }

        return $categoryName;
    }

    private function findCategoryByName($categories, string $categoryName)
    {
        $target = Str::lower(trim($categoryName));
        if ($target === '') {
            return null;
        }

        return $categories->first(function ($existingCategory) use ($target) {
            $categoryTitle = Str::lower(trim((string) ($existingCategory->category_title ?? '')));
            $categoryValue = Str::lower(trim((string) ($existingCategory->category ?? '')));

            return $categoryTitle === $target || $categoryValue === $target;
        });
    }

    /**
     * Pre-fill preview rows with generated SET_UNIQ_NO (PBL-...).
     */
    private function assignPreviewSetUniqNumbers(array &$csvData, int $orgId, $branchId): void
    {
        $lastCodeNumber = $this->resolveMaxInventorySequenceNumber($orgId, $branchId);
        $generatedInPreview = [];

        foreach ($csvData as &$row) {
            $provided = $this->normalizeProvidedSetUniqNo((string) ($row['set_uniq_no'] ?? ''));
            $itemId = isset($row['item_id']) ? (int) $row['item_id'] : null;

            if ($provided !== '' && !$this->setUniqNoConflictsWithExisting($provided, $orgId, $branchId, $itemId ?: null)) {
                $row['set_uniq_no'] = $provided;
                $row['set_uniq_auto'] = false;
                $generatedInPreview[] = $provided;
                continue;
            }

            $itemTitle = (string) ($row['item_name'] ?? '');
            $row['set_uniq_no'] = $this->generateNextSetUniqNo(
                $orgId,
                $branchId,
                $row['category_id'] ?? null,
                $lastCodeNumber,
                $itemTitle,
                $generatedInPreview
            );
            $row['set_uniq_auto'] = true;
        }
        unset($row);
    }

    private function resolveSetUniqNoForImport(
        string $providedSetUniqNo,
        int $orgId,
        $branchId,
        int $itemId,
        $existingInventoryRow,
        int &$lastCodeNumber,
        array &$generatedSetCodesInRequest,
        string $itemTitle
    ): string {
        $providedSetUniqNo = $this->normalizeProvidedSetUniqNo($providedSetUniqNo);

        if ($providedSetUniqNo !== '') {
            $existingId = $existingInventoryRow ? (int) $existingInventoryRow->id : null;
            if (!$this->setUniqNoConflictsWithExisting($providedSetUniqNo, $orgId, $branchId, $itemId, $existingId)
                && !in_array($providedSetUniqNo, $generatedSetCodesInRequest, true)) {
                $generatedSetCodesInRequest[] = $providedSetUniqNo;
                return $providedSetUniqNo;
            }
        }

        return $this->generateNextSetUniqNo(
            $orgId,
            $branchId,
            null,
            $lastCodeNumber,
            $itemTitle,
            $generatedSetCodesInRequest
        );
    }

    /**
     * True when code already belongs to another main package/item in inventory.
     */
    private function setUniqNoConflictsWithExisting(
        string $setUniqNo,
        int $orgId,
        $branchId,
        ?int $itemId,
        ?int $allowInventoryId = null
    ): bool {
        $query = DB::table('item_inventory')
            ->where('org_id', $orgId)
            ->where('status', '<>', 9)
            ->where('set_uniq_no', $setUniqNo);

        if ($branchId !== null && $branchId !== '') {
            $query->where('branch_id', $branchId);
        }

        if ($allowInventoryId) {
            $query->where('id', '<>', $allowInventoryId);
        }

        $existing = $query->get(['id', 'item_id']);
        if ($existing->isEmpty()) {
            return false;
        }

        if ($itemId === null) {
            return true;
        }

        // Allow same set_uniq_no for component rows (RAM/HDD/SSD/CPU) of a package.
        $componentItemIds = [12, 14, 17, 41];
        foreach ($existing as $row) {
            $existingItemId = (int) ($row->item_id ?? 0);
            if ($existingItemId === $itemId) {
                continue;
            }
            if (in_array($existingItemId, $componentItemIds, true)) {
                continue;
            }
            return true;
        }

        return false;
    }

    private function buildCsvExportRow(array $row, string $setUniqNo, array $itemData, string $importStatus): array
    {
        return [
            'import_status' => $importStatus,
            'set_uniq_no' => $setUniqNo,
            'stock_in_no' => $itemData['stock_in_no'] ?? '',
            'item_name' => $row['item_name'] ?? '',
            'category_name' => $row['category_name'] ?? '',
            'brand_name' => $row['brand_name'] ?? '',
            'supplier_name' => $row['supplier_name'] ?? '',
            'serial_no' => $row['serial_no'] ?? '',
            'quantity' => $row['quantity'] ?? 1,
            'unit_size' => $row['unit_size'] ?? '',
            'price' => $row['price'] ?? 0,
            'condition' => $row['condition'] ?? '',
            'ram' => $row['ram'] ?? '',
            'storage' => $row['storage'] ?? '',
            'processor' => $row['processor'] ?? '',
            'description' => $row['description'] ?? '',
        ];
    }

    public function downloadLastImportResult()
    {
        $rows = session('csv_upload_export_rows', []);
        if (empty($rows)) {
            return Redirect::route('items-inventory-csv-upload')
                ->with('eMessage', 'No recent upload export found.');
        }

        session()->forget('csv_upload_export_rows');

        $headers = [
            'Import Status',
            'SET_UNIQ_NO',
            'STOCK_IN_NO',
            'Item Name',
            'Category',
            'Brand',
            'Supplier',
            'Serial No',
            'Quantity',
            'Unit',
            'Price',
            'Condition',
            'RAM',
            'Storage',
            'Processor',
            'Description',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Upload Result');
        $sheet->fromArray($headers, null, 'A1');

        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                $row['import_status'] ?? '',
                $row['set_uniq_no'] ?? '',
                $row['stock_in_no'] ?? '',
                $row['item_name'] ?? '',
                $row['category_name'] ?? '',
                $row['brand_name'] ?? '',
                $row['supplier_name'] ?? '',
                $row['serial_no'] ?? '',
                $row['quantity'] ?? '',
                $row['unit_size'] ?? '',
                $row['price'] ?? '',
                $row['condition'] ?? '',
                $row['ram'] ?? '',
                $row['storage'] ?? '',
                $row['processor'] ?? '',
                $row['description'] ?? '',
            ];
        }
        if (!empty($data)) {
            $sheet->fromArray($data, null, 'A2');
        }

        $sheet->getStyle('A1:P1')->getFont()->setBold(true);
        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'inventory_upload_result_' . now()->format('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
            'Expires' => '0',
        ]);
    }

    /**
     * Only official PBL-style codes from file/user are treated as manual SET_UNIQ_NO.
     */
    private function normalizeProvidedSetUniqNo(string $code): string
    {
        $code = trim($code);
        if ($code === '') {
            return '';
        }

        $lower = strtolower($code);
        if (in_array($lower, ['-', 'n/a', 'na', 'nil', 'none', 'null'], true)) {
            return '';
        }

        if (preg_match('/^PBL-/i', $code)) {
            return $code;
        }

        return '';
    }

    /**
     * Cancel CSV upload and clear session.
     */
    public function cancelUpload()
    {
        session()->forget('csv_preview_data');
        session()->forget('csv_errors');
        session()->forget('csv_import_meta');
        session()->forget('csv_upload_branch_id');

        return Redirect::to('items-inventory')->with('success', 'CSV upload cancelled.');
    }
}
