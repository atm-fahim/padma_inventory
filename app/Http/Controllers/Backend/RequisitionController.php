<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Interfaces\Repo\Backend\RequisitionRepo;
use App\Interfaces\Repo\Backend\ItemsRepo;
use App\Traits\UserAccessTraits;
use App\Traits\CommonTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequisitionController extends Controller
{
    use UserAccessTraits, CommonTraits;

    private RequisitionRepo $requisitionRepo;
    private ItemsRepo $itemsRepo;

    public function __construct(RequisitionRepo $requisitionRepo, ItemsRepo $itemsRepo)
    {
        $this->middleware('auth');
        $this->requisitionRepo = $requisitionRepo;
        $this->itemsRepo = $itemsRepo;
    }

    public function index()
    {
        $userAccessData = $this->userAccessFunction();
        $orgId = Auth::user()->org_id ?? 1;
        $items = $this->itemsRepo->itemAllInfoByOrgId($orgId);
        if (method_exists($this->itemsRepo, 'itemInfoByOrgId')) {
            $items = $this->itemsRepo->itemInfoByOrgId();
        }
        return view('backend.quotation.requisition')
            ->with('itemsData', $items)
            ->with('user_access', $userAccessData);
    }

    public function store(Request $request)
    {
        $validation = $this->requisitionRepo->checkRequestValidity($request);
        if (!$validation['isValidationSuccess']) {
            return back()->withErrors($validation['error'])->withInput();
        }

        $orgId = Auth::user()->org_id ?? 1;
        $branchId = Auth::user()->branch_id ?? null;

        // Get employee info (authoritative)
        $employeeId = $request->input('employee_id');
        $employeeName = '';
        if (!empty($employeeId)) {
            $endPointUrl = "/api/v3/user-info-padmaportal?emp_id=" . $employeeId;
            $employee = json_decode($this->employeeInfo($endPointUrl));
            $employeeName = $employee->emp_name ?? '';
        }

        $requisitionData = [
            'org_id' => $orgId,
            'branch_id' => $branchId,
            'requisition_no' => $this->requisitionRepo->generateRequisitionNo(),
            'requisition_date' => $request->input('requisition_date'),
            'employee_id' => $employeeId,
            'employee_name' => $employeeName,
            'notes' => $request->input('notes'),
            'status' => 2, // submitted
        ];

        $itemsData = [];
        $itemNames = $request->input('item_name', []);
        $quantities = $request->input('quantity', []);
        $uoms = $request->input('uom', []);

        foreach ($itemNames as $index => $name) {
            if (empty($name)) continue;
            $itemsData[] = [
                'item_name' => $name,
                'quantity' => $quantities[$index] ?? 1,
                'uom' => $uoms[$index] ?? 'PCS',
                'status' => 1,
            ];
        }

        $requisition = $this->requisitionRepo->createWithItems($requisitionData, $itemsData);
        return redirect()->route('requisition-list')->with('success', 'Requisition created. No: ' . $requisition->requisition_no);
    }

    public function list()
    {
        $userAccessData = $this->userAccessFunction();
        $orgId = Auth::user()->org_id ?? 1;
        $branchId = Auth::user()->branch_id ?? null;
        $requisitions = $this->requisitionRepo->getRequisitionList($orgId, $branchId);
        return view('backend.quotation.requisition_list')
            ->with('requisitions', $requisitions)
            ->with('user_access', $userAccessData);
    }

    public function show($id)
    {
        $requisition = $this->requisitionRepo->findWithItemsById($id);
        return view('backend.quotation.requisition_show')->with('requisition', $requisition);
    }
}
