<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Backend\{
    DashboardLogController,
    UserPermissionController,
    UsersController,
    UsersTypeController,
    MenuController,
    OrganizationController,
    BranchController,
    CategoryController,
    WarehouseController,
    SupplierController,
    UnitController,
    BrandController,
    ItemsInventoryController,
    ItemsCsvUploadController,
    ItemsCsvReceiveController,
    ItemsReceiveController,
    ItemController,
    DivisionController,
    DepartmentController,
    StockOutController,
    ItemSwapController,
    StockInController,
    InventoryReportController,
    InventoryBarcodeController,
    DeliveryChallanController,
    ProductReturnController,
    RequisitionController,
    QuotationController,
    TaskLoginController,
    HomeController as BackendHomeController
};

Route::get('/', [HomeController::class, 'index']);
// Route::get('/task_login', [TaskLoginController::class, 'index']);
Route::get('/login', [LoginController::class, 'loginCheck'])->name('login');

Auth::routes(['register' => false]);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
Route::get('/task-login', [TaskLoginController::class, 'index'])->name('task-login');

Route::controller(DashboardLogController::class)->group(function () {
    Route::get('/dashboard-total-count-log', 'dashBoardTotalLog')->name('dashboard-total-count-log');
    Route::get('/dashboard-category-details', 'getCategoryDetails')->name('dashboard-category-details');
    Route::get('/dashboard-item-details', 'getItemDetails')->name('dashboard-item-details');
    Route::get('/dashboard-supplier-details', 'getSupplierDetails')->name('dashboard-supplier-details');
});

/* User Permission */
Route::controller(UserPermissionController::class)->group(function () {
    Route::get('/permission', 'index')->name('permission');
    Route::get('/show-permission-menu/{permission_type}', 'getPermissionMenu');
    Route::post('/give-permission', 'givePermission')->name('give-permission');
});

/* User section */
Route::controller(UsersController::class)->group(function () {
    Route::get('/users', 'index')->name('users');
    Route::post('/user-save-update', 'saveUpdate')->name('user-save-update');
    Route::get('/user-edit/{id}', 'edit');
    Route::get('/user-delete/{id}', 'delete')->name('user-delete');
    Route::get('/user-in-active/{id}', 'inActive')->name('user-in-active');
    Route::get('/user-active/{id}', 'active')->name('user-active');
});

/* User type section */
Route::controller(UsersTypeController::class)->group(function () {
    Route::get('/user-type', 'index')->name('user-type');
    Route::post('/user-type-save-update', 'saveUpdate')->name('user-type-save-update');
    Route::get('/user-type-edit/{id}', 'edit');
    Route::get('/user-type-delete/{id}', 'delete')->name('user-type-delete');
    Route::get('/user-type-in-active/{id}', 'inActive')->name('user-type-in-active');
    Route::get('/user-type-active/{id}', 'active')->name('user-type-active');
});

/* Backend Menu section */
Route::controller(MenuController::class)->group(function () {
    Route::get('/backend-menu', 'index')->name('backend-menu');
    Route::post('/backend-menu-save-update', 'saveUpdate')->name('backend-menu-save-update');
    Route::get('/backend-menu-edit/{id}', 'edit');
    Route::get('/backend-menu-delete/{id}', 'delete')->name('backend-menu-delete');
    Route::get('/backend-menu-in-active/{id}', 'inActive')->name('backend-menu-in-active');
    Route::get('/backend-menu-active/{id}', 'active')->name('backend-menu-active');
});

/* Organization section */
Route::controller(OrganizationController::class)->group(function () {
    Route::get('/organization', 'index')->name('organization');
    Route::post('/organization-save-update', 'saveUpdate')->name('organization-save-update');
    Route::get('/organization-edit/{id}', 'edit');
    Route::get('/organization-delete/{id}', 'delete')->name('organization-delete');
    Route::get('/organization-in-active/{id}', 'inActive')->name('organization-in-active');
    Route::get('/organization-active/{id}', 'active')->name('organization-active');
});

/* Branch section */
Route::controller(BranchController::class)->group(function () {
    Route::get('/branch', 'index')->name('branch');
    Route::post('/branch-save-update', 'saveUpdate')->name('branch-save-update');
    Route::get('/branch-edit/{id}', 'edit');
    Route::get('/branch-delete/{id}', 'delete')->name('branch-delete');
    Route::get('/branch-in-active/{id}', 'inActive')->name('branch-in-active');
    Route::get('/branch-active/{id}', 'active')->name('branch-active');
});

/* Category section */
Route::controller(CategoryController::class)->group(function () {
    Route::get('/category', 'index')->name('category');
    Route::post('/category-save-update', 'saveUpdate')->name('category-save-update');
    Route::post('/category-import-excel', 'importExcel')->name('category-import-excel');
    Route::get('/category-edit/{id}', 'edit');
    Route::get('/category-delete/{id}', 'delete')->name('category-delete');
    Route::get('/category-in-active/{id}', 'inActive')->name('category-in-active');
    Route::get('/category-active/{id}', 'active')->name('category-active');
});

/* Warehouse section */
Route::controller(WarehouseController::class)->group(function () {
    Route::get('/add-warehouse', 'index')->name('add-warehouse');
    Route::post('/warehouse-save', 'saveUpdate')->name('warehouse-save');
    Route::post('/warehouse-update', 'warehouseUpdate')->name('warehouse-update');
    Route::get('/warehouse-delete/{id}', 'delete')->name('warehouse-delete');
    Route::get('/warehouse-in-active/{id}', 'inActive')->name('warehouse-in-active');
    Route::get('/warehouse-active/{id}', 'active')->name('warehouse-active');
    Route::get('/warehouse-invoice-details/{invoiceNo}', 'invoiceDetails')->name('warehouse-invoice-details');
    Route::get('/warehouse-edit/{invoiceNo}', 'warehouseEdit')->name('warehouse-edit');
    Route::post('/warehouse-invoice-cancel', 'warehouseCancel')->name('warehouse-invoice-cancel');
});

/* Supplier section */
Route::controller(SupplierController::class)->group(function () {
    Route::get('/supplier', 'index')->name('supplier');
    Route::post('/supplier-save-update', 'saveUpdate')->name('supplier-save-update');
    Route::get('/supplier-edit/{id}', 'edit');
    Route::get('/supplier-delete/{id}', 'delete')->name('supplier-delete');
    Route::get('/supplier-in-active/{id}', 'inActive')->name('supplier-in-active');
    Route::get('/supplier-active/{id}', 'active')->name('supplier-active');
});

/* Size section */
Route::controller(UnitController::class)->group(function () {
    Route::get('/size-unit', 'index')->name('size-unit');
    Route::post('/size-unit-save-update', 'saveUpdate')->name('size-unit-save-update');
    Route::get('/size-unit-edit/{id}', 'edit');
    Route::get('/size-unit-delete/{id}', 'delete')->name('size-unit-delete');
    Route::get('/size-unit-in-active/{id}', 'inActive')->name('size-unit-in-active');
    Route::get('/size-unit-active/{id}', 'active')->name('size-unit-active');
});

/* Brand / generic section */
Route::controller(BrandController::class)->group(function () {
    Route::get('/brand', 'index')->name('brand');
    Route::post('/brand-save-update', 'saveUpdate')->name('brand-save-update');
    Route::get('/brand-edit/{id}', 'edit');
    Route::get('/brand-delete/{id}', 'delete')->name('brand-delete');
    Route::get('/brand-in-active/{id}', 'inActive')->name('brand-in-active');
    Route::get('/brand-active/{id}', 'active')->name('brand-active');
});

/* Item receive section */
Route::controller(ItemsInventoryController::class)->group(function () {
    Route::get('/items-inventory', 'index')->name('items-inventory');
    Route::get('/items-inventory-list', 'list')->name('items-inventory-list');
    Route::post('/items-inventory-save-update', 'saveUpdate')->name('items-inventory-save-update');
    Route::get('/items-inventory-edit/{id}', 'edit')->name('items-inventory-edit');
    Route::post('/items-inventory-update/{id}', 'update')->name('items-inventory-update');
    Route::get('/items-inventory-delete/{id}', 'delete')->name('items-inventory-delete');
    Route::get('/items-inventory-in-active/{id}', 'inActive')->name('items-inventory-in-active');
    Route::get('/items-inventory-active/{id}', 'active')->name('items-inventory-active');
});

/* Item inventory CSV upload section */
Route::controller(ItemsCsvUploadController::class)->group(function () {
    Route::get('/items-inventory-csv-upload', 'index')->name('items-inventory-csv-upload');
    Route::get('/items-inventory-csv-sample/{format?}', 'downloadSample')->name('items-inventory-csv-sample');
    Route::post('/items-inventory-csv-upload-process', 'uploadCsv')->name('items-inventory-csv-upload-process');
    Route::post('/items-inventory-csv-store', 'storeCsv')->name('items-inventory-csv-store');
    Route::get('/items-inventory-csv-cancel', 'cancelUpload')->name('items-inventory-csv-cancel');
    Route::get('/items-inventory-csv-export-result', 'downloadLastImportResult')->name('items-inventory-csv-export-result');
});

/* Item inventory CSV receive section */
Route::controller(ItemsCsvReceiveController::class)->group(function () {
    Route::get('/items-inventory-csv-receive', 'index')->name('items-inventory-csv-receive');
    Route::post('/items-inventory-csv-receive-save', 'store')->name('items-inventory-csv-receive-save');
});
/* Item section */
Route::controller(ItemController::class)->group(function () {
    Route::get('/items', 'index')->name('items');
    Route::post('/item-save-update', 'saveUpdate')->name('item-save-update');
    Route::get('/items-by-category/{categoryId}', 'itemsByCategory')->name('items-by-category');
    Route::get('/item-edit/{id}', 'edit');
    Route::get('/item-delete/{id}', 'delete')->name('item-delete');
    Route::get('/item-in-active/{id}', 'inActive')->name('item-in-active');
    Route::get('/item-active/{id}', 'active')->name('item-active');
});

/* Division section */
Route::controller(DivisionController::class)->group(function () {
    Route::get('/division', 'index')->name('division');
    Route::post('/division-save-update', 'saveUpdate')->name('division-save-update');
    Route::get('/division-edit/{id}', 'edit');
    Route::get('/division-delete/{id}', 'delete')->name('division-delete');
    Route::get('/division-in-active/{id}', 'inActive')->name('division-in-active');
    Route::get('/division-active/{id}', 'active')->name('division-active');
});

/* Department section */
Route::controller(DepartmentController::class)->group(function () {
    Route::get('/department', 'index')->name('department');
    Route::post('/department-save-update', 'saveUpdate')->name('department-save-update');
    Route::get('/department-edit/{id}', 'edit');
    Route::get('/department-delete/{id}', 'delete')->name('department-delete');
    Route::get('/department-in-active/{id}', 'inActive')->name('department-in-active');
    Route::get('/department-active/{id}', 'active')->name('department-active');
    Route::get('/department-by-division-id/{id}', 'departmentInfoByDivisionId')->name('department-by-division-id');
});

/* Stock Out section */
Route::controller(StockOutController::class)->group(function () {
    Route::get('/stock-out', 'index')->name('stock-out');
    Route::get('/stock-out-list', 'stockOutList')->name('stock-out-list');
    Route::get('/stock-out-invoice/{stockOutNo}', 'viewInvoice')->name('stock-out-invoice');
    Route::post('/stock-out-gate-pass-print-log', 'logGatePassPrint')->name('stock-out-gate-pass-print-log');
    Route::get('/receive-stock', 'receiveStock')->name('receive-stock');
    Route::post('/receive-stock-save', 'saveReceiveStock')->name('receive-stock-save');
    Route::get('/transfer-items', 'transferItems')->name('transfer-items');
    Route::post('/transfer-items-save', 'saveTransferItems')->name('transfer-items-save');
    Route::get('/return-to-division', 'returnToDivision')->name('return-to-division');
    Route::post('/return-to-division-save', 'saveReturnToDivision')->name('return-to-division-save');
    Route::post('/stock-out-save-update', 'saveUpdate')->name('stock-out-save-update');
    Route::post('/get-employee-by-id', 'getEmployeeByEmployeeId')->name('get-employee-by-id');
    Route::post('/get-inventory-by-item-id', 'getInventoryByItemId')->name('get-inventory-by-item-id');
    Route::post('/get-items-by-set-uniq-no', 'getItemsBySetUniqNo')->name('get-items-by-set-uniq-no');
    Route::post('/stock-out-branch-set-uniq-list', 'getBranchSetUniqNoList')->name('stock-out-branch-set-uniq-list');
});

/* Item Swap section */
Route::controller(ItemSwapController::class)->group(function () {
    Route::get('/swap-items', 'swapItems')->name('swap-items');
    Route::post('/swap-items-save', 'saveSwapItems')->name('swap-items-save');
    Route::get('/receive-swap-items/{swapNo?}', 'receiveSwapItems')->name('receive-swap-items');
    Route::post('/receive-swap-items-save', 'saveReceiveSwapItems')->name('receive-swap-items-save');
});

/* Stock In section */
Route::controller(StockInController::class)->group(function () {
    Route::get('/stock-in', 'index')->name('stock-in');
    Route::get('/stock-in-list', 'stockInList')->name('stock-in-list');
    Route::get('/stock-in-invoice/{stockInNo}', 'viewInvoice')->name('stock-in-invoice');
    Route::post('/stock-in-save-update', 'saveUpdate')->name('stock-in-save-update');
    Route::post('/stock-in-get-inventory-by-item-id', 'getInventoryByItemId')->name('stock-in-get-inventory-by-item-id');
    Route::post('/stock-in-get-items-by-set-uniq-no', 'getItemsBySetUniqNo')->name('stock-in-get-items-by-set-uniq-no');
});

/* Inventory barcode (SET_UNIQ_NO + item name) */
Route::controller(InventoryBarcodeController::class)->group(function () {
    Route::get('/inventory-barcode', 'index')->name('inventory-barcode');
    Route::get('/barcode-generate', 'index')->name('barcode-generate');
    Route::post('/inventory-barcode-generate', 'generate')->name('inventory-barcode-generate');
    Route::post('/barcode-generate-by-item-id', 'generate')->name('barcode-generate-by-item-id');
});

/* Inventory Report (Stock In / Stock Out / Total Qty) */
Route::controller(InventoryReportController::class)->group(function () {
    Route::get('/inventory-report', 'index')->name('inventory-report');
    Route::get('/inventory-report-details', 'getDetails')->name('inventory-report-details');
    Route::get('/inventory-detail-report', 'detailReport')->name('inventory-detail-report');
    Route::get('/inventory-summary', 'summary')->name('inventory-summary');
    Route::get('/inventory-division-branch-summary', 'summary')->name('inventory-division-branch-summary');
    Route::get('/inventory-detail-export-excel', 'exportExcel')->name('inventory-detail-export-excel');
    Route::get('/inventory-detail-export-pdf', 'exportPdf')->name('inventory-detail-export-pdf');
    Route::get('/inventory-summary-export-excel', 'summaryExportExcel')->name('inventory-summary-export-excel');
    Route::get('/inventory-summary-export-pdf', 'summaryExportPdf')->name('inventory-summary-export-pdf');
    Route::get('/inventory-employee-report', 'employeeReport')->name('inventory-employee-report');
    Route::get('/inventory-employee-report-export-excel', 'employeeReportExportExcel')->name('inventory-employee-report-export-excel');
    Route::get('/inventory-employee-report-export-pdf', 'employeeReportExportPdf')->name('inventory-employee-report-export-pdf');
});

/* Delivery Challan section */
Route::controller(DeliveryChallanController::class)->group(function () {
    Route::get('/delivery-challan', 'index')->name('delivery-challan');
    Route::post('/delivery-challan-store', 'store')->name('delivery-challan-store');
    Route::get('/delivery-challan-show/{id}', 'show')->name('delivery-challan-show');
});

/* Product Repair section */
Route::controller(ProductReturnController::class)->group(function () {
    Route::get('/product-repair', 'index')->name('product-repair');
    Route::post('/product-repair-store', 'store')->name('product-repair-store');
    Route::get('/product-repair-list', 'list')->name('product-repair-list');
    Route::get('/product-repair-show/{id}', 'show')->name('product-repair-show');
});

/* Quotation section */
Route::controller(QuotationController::class)->group(function () {
    Route::get('/create-quotation', 'index')->name('create-quotation');
    Route::post('/quotation-store', 'store')->name('quotation-store');
    Route::get('/quotation-list', 'list')->name('quotation-list');
    Route::get('/quotation-show/{id}', 'show')->name('quotation-show');
    Route::post('/quotation-approve/{id}', 'approve')->name('quotation-approve');
});

/* Requisition section */
Route::controller(RequisitionController::class)->group(function () {
    Route::get('/create-requisition', 'index')->name('create-requisition');
    Route::post('/requisition-store', 'store')->name('requisition-store');
    Route::get('/requisition-list', 'list')->name('requisition-list');
    Route::get('/requisition-show/{id}', 'show')->name('requisition-show');
});

/* Fallback / hash route */
Route::get('/#', [BackendHomeController::class, 'index'])->name('#');



//Route::get('/', function () {
////    try {
////        $dbconnect = DB::connection()->getPDO();
////        $dbname = DB::connection()->getDatabaseName();
////        echo "Connected successfully to the database. Database name is :".$dbname;
////    } catch(Exception $e) {
////        echo "Error in connecting to the database";
////    }
//    return view('welcome');
//});
//
//Auth::routes();
//
//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
