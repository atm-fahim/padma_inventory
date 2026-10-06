<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Interfaces\Repo\Backend\ProductReturnRepo;
use App\Interfaces\Repo\Backend\ItemsRepo;
use App\Interfaces\Repo\Backend\SupplierRepo;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductReturnController extends Controller
{
    use UserAccessTraits;

    private ProductReturnRepo $productReturnRepo;
    private ItemsRepo $itemsRepo;
    private SupplierRepo $supplierRepo;

    public function __construct(
        ProductReturnRepo $productReturnRepo,
        ItemsRepo $itemsRepo,
        SupplierRepo $supplierRepo
    ) {
        $this->middleware('auth');
        $this->productReturnRepo = $productReturnRepo;
        $this->itemsRepo = $itemsRepo;
        $this->supplierRepo = $supplierRepo;
    }

    public function index()
    {
        $userAccessData = $this->userAccessFunction();
        $orgId = Auth::user()->org_id ?? 1;
        $branchId = Auth::user()->branch_id ?? null;

        $items = $this->itemsRepo->withoutDeletingData();
        $suppliers = $this->supplierRepo->withoutDeletingData();

        return view('backend.item.product_return')
            ->with('itemsData', $items)
            ->with('suppliers', $suppliers)
            ->with('user_access', $userAccessData);
    }

    public function store(Request $request)
    {
        $validation = $this->productReturnRepo->checkRequestValidity($request);
        if (!$validation['isValidationSuccess']) {
            return back()
                ->withErrors($validation['error'])
                ->withInput();
        }

        $orgId = Auth::user()->org_id ?? 1;
        $branchId = Auth::user()->branch_id ?? null;

        $returnData = [
            'org_id'           => $orgId,
            'branch_id'        => $branchId,
            'supplier_id'      => $request->input('supplier_id'),
            'return_no'        => $this->productReturnRepo->generateReturnNo(),
            'return_date'      => $request->input('return_date'),
            'return_type'      => $request->input('return_type'),
            'reason'           => $request->input('reason'),
            'remarks'          => $request->input('remarks'),
            'reference_no'     => $request->input('reference_no'),
            'status'           => 1, // pending
        ];

        $itemsData = [];
        $itemNames   = $request->input('item_name', []);
        $descriptions = $request->input('description', []);
        $quantities  = $request->input('quantity', []);
        $uoms        = $request->input('uom', []);
        $damageDescriptions = $request->input('damage_description', []);
        $repairNotes = $request->input('repair_notes', []);

        foreach ($itemNames as $index => $name) {
            if (empty($name)) {
                continue;
            }

            $itemsData[] = [
                'item_name'          => $name,
                'description'        => $descriptions[$index] ?? null,
                'quantity'           => $quantities[$index] ?? 1,
                'uom'                => $uoms[$index] ?? 'PCS',
                'damage_description' => $damageDescriptions[$index] ?? null,
                'repair_notes'       => $repairNotes[$index] ?? null,
                'status'             => 1,
            ];
        }

        $productReturn = $this->productReturnRepo->createWithItems($returnData, $itemsData);

        return redirect()
            ->route('product-repair-list')
            ->with('success', 'Product repair created successfully. Return No: ' . $productReturn->return_no);
    }

    public function list()
    {
        $userAccessData = $this->userAccessFunction();
        $orgId = Auth::user()->org_id ?? 1;
        $branchId = Auth::user()->branch_id ?? null;

        $returns = $this->productReturnRepo->getReturnList($orgId, $branchId);

        return view('backend.item.product_return_list')
            ->with('returns', $returns)
            ->with('user_access', $userAccessData);
    }

    public function show($id)
    {
        $return = $this->productReturnRepo->findWithItemsById($id);
        return view('backend.item.product_return_view')
            ->with('return', $return);
    }
}
