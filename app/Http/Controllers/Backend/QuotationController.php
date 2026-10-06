<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Interfaces\Repo\Backend\QuotationRepo;
use App\Interfaces\Repo\Backend\ItemsRepo;
use App\Interfaces\Repo\Backend\SupplierRepo;
use App\Traits\UserAccessTraits;
use App\Traits\CommonTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class QuotationController extends Controller
{
    use UserAccessTraits, CommonTraits;

    private QuotationRepo $quotationRepo;
    private ItemsRepo $itemsRepo;
    private SupplierRepo $supplierRepo;

    public function __construct(
        QuotationRepo $quotationRepo,
        ItemsRepo $itemsRepo,
        SupplierRepo $supplierRepo
    ) {
        $this->middleware('auth');
        $this->quotationRepo = $quotationRepo;
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

        return view('backend.item.quotation')
            ->with('itemsData', $items)
            ->with('suppliers', $suppliers)
            ->with('user_access', $userAccessData);
    }

    public function store(Request $request)
    {
        $validation = $this->quotationRepo->checkRequestValidity($request);
        if (!$validation['isValidationSuccess']) {
            return back()
                ->withErrors($validation['error'])
                ->withInput();
        }

        $orgId = Auth::user()->org_id ?? 1;
        $branchId = Auth::user()->branch_id ?? null;

        // Get employee info
        $employeeId = $request->input('employee_id');
        $employeeName = '';
        if (!empty($employeeId)) {
            $endPointUrl = "/api/v3/user-info-padmaportal?emp_id=" . $employeeId;
            $employee = json_decode($this->employeeInfo($endPointUrl));
            $employeeName = $employee->emp_name ?? '';
        }

        // Calculate totals
        $itemIds = $request->input('item_id', []);
        $quantities = $request->input('quantity', []);
        $unitPrices = $request->input('unit_price', []);
        $discounts = $request->input('discount', []);
        $vats = $request->input('vat', []);
        $taxes = $request->input('tax', []);

        $subtotal = 0;
        $totalDiscount = 0;
        $totalVat = 0;
        $totalTax = 0;

        foreach ($itemIds as $index => $itemId) {
            $qty = (float)($quantities[$index] ?? 0);
            $price = (float)($unitPrices[$index] ?? 0);
            $discount = (float)($discounts[$index] ?? 0);
            $vat = (float)($vats[$index] ?? 0);
            $tax = (float)($taxes[$index] ?? 0);

            $lineTotal = ($qty * $price) - $discount + $vat + $tax;
            $subtotal += ($qty * $price);
            $totalDiscount += $discount;
            $totalVat += $vat;
            $totalTax += $tax;
        }

        $totalAmount = $subtotal - $totalDiscount + $totalVat + $totalTax;

        $quotationData = [
            'org_id'           => $orgId,
            'branch_id'        => $branchId,
            'supplier_id'      => $request->input('supplier_id'),
            'quotation_no'     => $this->quotationRepo->generateQuotationNo(),
            'quotation_date'   => $request->input('quotation_date'),
            'employee_id'      => $employeeId,
            'employee_name'    => $employeeName,
            'subtotal'         => $subtotal,
            'discount_amount'  => $totalDiscount,
            'vat_amount'       => $totalVat,
            'tax_amount'       => $totalTax,
            'total_amount'     => $totalAmount,
            'note'             => $request->input('note'),
            'status'           => 'pending',
            'active_status'    => 1,
        ];

        $itemsData = [];
        $itemNames = $request->input('item_name', []);
        $descriptions = $request->input('description', []);

        foreach ($itemIds as $index => $itemId) {
            if (empty($itemId)) {
                continue;
            }

            $qty = (float)($quantities[$index] ?? 0);
            $price = (float)($unitPrices[$index] ?? 0);
            $discount = (float)($discounts[$index] ?? 0);
            $vat = (float)($vats[$index] ?? 0);
            $tax = (float)($taxes[$index] ?? 0);

            $lineTotal = ($qty * $price) - $discount + $vat + $tax;

            $itemsData[] = [
                'item_id'     => $itemId,
                'item_name'   => $itemNames[$index] ?? '',
                'description' => $descriptions[$index] ?? null,
                'quantity'    => $qty,
                'uom'         => $request->input('uom')[$index] ?? 'PCS',
                'unit_price'  => $price,
                'discount'    => $discount,
                'vat'         => $vat,
                'tax'         => $tax,
                'line_total'  => $lineTotal,
                'status'      => 1,
            ];
        }

        $quotation = $this->quotationRepo->createWithItems($quotationData, $itemsData);

        return redirect()
            ->route('quotation-list')
            ->with('success', 'Quotation created successfully. Quotation No: ' . $quotation->quotation_no);
    }

    public function list()
    {
        $userAccessData = $this->userAccessFunction();
        $orgId = Auth::user()->org_id ?? 1;
        $branchId = Auth::user()->branch_id ?? null;

        $quotations = $this->quotationRepo->getQuotationList($orgId, $branchId);

        return view('backend.item.quotation_list')
            ->with('quotations', $quotations)
            ->with('user_access', $userAccessData);
    }

    public function show($id)
    {
        $quotation = $this->quotationRepo->findWithItemsById($id);

        $currentUserApprovalLevel = $this->resolveApprovalLevelForCurrentUser();
        $nextApprovalLevel = $this->nextApprovalLevelFromStatus($quotation->status);
        $canApprove = !empty($currentUserApprovalLevel) && $currentUserApprovalLevel === $nextApprovalLevel;

        return view('backend.item.quotation_view')
            ->with('quotation', $quotation)
            ->with('currentUserApprovalLevel', $currentUserApprovalLevel)
            ->with('nextApprovalLevel', $nextApprovalLevel)
            ->with('canApprove', $canApprove);
    }

    public function approve(Request $request, $id)
    {
        $quotation = $this->quotationRepo->findById($id);

        // Enforce role-based + sequential approval
        $approvalLevel = $this->resolveApprovalLevelForCurrentUser();
        if (empty($approvalLevel)) {
            abort(Response::HTTP_FORBIDDEN, 'You are not allowed to approve quotations.');
        }

        $expectedLevel = $this->nextApprovalLevelFromStatus($quotation->status);
        if ($approvalLevel !== $expectedLevel) {
            return redirect()
                ->route('quotation-show', $id)
                ->with('error', 'Approval not allowed. Next required approval: ' . ($expectedLevel ?? 'N/A'));
        }

        $status = $request->input('status'); // 'approved' or 'rejected'
        $comments = $request->input('comments');

        $approverInfo = [
            'user_id' => auth()->id(),
            'name' => auth()->user()->name ?? '',
            'designation' => $request->input('designation', ''),
        ];

        $this->quotationRepo->approveQuotation($id, $approvalLevel, $status, $comments, $approverInfo);

        return redirect()
            ->route('quotation-show', $id)
            ->with('success', 'Quotation ' . ucfirst($status) . ' successfully');
    }

    private function nextApprovalLevelFromStatus(?string $status): ?string
    {
        return match ($status) {
            'draft', 'pending' => 'admin',
            'admin_approved' => 'division_head',
            'division_head_approved' => 'md',
            'md_approved' => 'board',
            default => null,
        };
    }

    /**
     * Resolve current user's approval level from user_types / user_access_type.
     * Returns one of: admin, division_head, md, board or null.
     */
    private function resolveApprovalLevelForCurrentUser(): ?string
    {
        $userType = auth()->user()->user_types ?? null;
        $text = '';

        try {
            if (is_numeric($userType)) {
                $row = DB::table('user_access_type')->where('id', (int)$userType)->first();
                $text = strtolower(($row->slug ?? $row->user_type ?? (string)$userType));
            } elseif (!empty($userType)) {
                $row = DB::table('user_access_type')
                    ->where('slug', $userType)
                    ->orWhere('user_type', $userType)
                    ->first();
                $text = strtolower(($row->slug ?? $row->user_type ?? (string)$userType));
            }
        } catch (\Throwable $e) {
            $text = strtolower((string)$userType);
        }

        if (str_contains($text, 'division') && str_contains($text, 'head')) return 'division_head';
        if ($text === 'md' || str_contains($text, 'managing') || str_contains($text, 'md')) return 'md';
        if (str_contains($text, 'board')) return 'board';
        if (str_contains($text, 'admin')) return 'admin';

        return null;
    }
}
