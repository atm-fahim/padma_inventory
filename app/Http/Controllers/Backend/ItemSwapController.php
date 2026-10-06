<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\BranchRepo;
use App\Interfaces\Repo\Backend\ItemSwapRepo;
use App\Traits\CommonTraits;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class ItemSwapController extends BaseController
{
    use UserAccessTraits;
    use CommonTraits;

    private ItemSwapRepo $itemSwapRepo;

    private BranchRepo $branchRepo;

    public function __construct(ItemSwapRepo $itemSwapRepo, BranchRepo $branchRepo)
    {
        $this->middleware('auth');
        $this->itemSwapRepo = $itemSwapRepo;
        $this->branchRepo = $branchRepo;
    }

    public function swapItems(Request $request)
    {
        try {
            $userAccessData = $this->userAccessFunction();
            $searchEmployeeId = trim((string) $request->input('employee_id', ''));
            $employeeInfo = null;
            $branchId = null;
            $swappableItems = collect();

            if ($searchEmployeeId !== '') {
                $endPointUrl = '/api/v3/user-info-padmaportal?emp_id=' . $searchEmployeeId;
                $employeeInfo = json_decode($this->employeeInfo($endPointUrl));

                if ($employeeInfo && isset($employeeInfo->branch_id)) {
                    $branch = DB::table('branch')
                        ->where('hr_branch_id', $employeeInfo->branch_id)
                        ->first();
                    $branchId = $branch->hr_branch_id ?? $employeeInfo->branch_id ?? null;
                }

                $swappableItems = $this->itemSwapRepo->getSwappableItemsByEmployee($searchEmployeeId, $branchId);
            }

            return view('backend.item.swap_items')
                ->with('swappableItems', $swappableItems)
                ->with('branchData', $this->branchRepo->withoutDeletingData())
                ->with('searchEmployeeId', $searchEmployeeId)
                ->with('employeeInfo', $employeeInfo)
                ->with('user_access', $userAccessData);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return Redirect::back();
        }
    }

    public function saveSwapItems(Request $request)
    {
        $validation = $this->itemSwapRepo->checkRequestValidityForSwap($request);
        if (!$validation['isValidationSuccess']) {
            session()->flash('error', $validation['error']);

            return Redirect::back()->withInput();
        }

        DB::beginTransaction();
        try {
            $fromEmployeeId = trim((string) $request->input('from_employee_id'));
            $toEmployeeId = trim((string) $request->input('to_employee_id'));
            $toBranchId = (int) $request->input('to_branch_id');
            $inventoryIds = $request->input('inventory_ids', []);
            $remarks = trim((string) $request->input('remarks', ''));
            $orgId = auth()->user()->org_id ?? null;

            $fromBranchId = null;
            $firstItem = DB::table('item_inventory')->where('id', $inventoryIds[0] ?? 0)->first();
            if ($firstItem) {
                $fromBranchId = $firstItem->to_branch_id ?? $firstItem->received_branch_id ?? auth()->user()->branch_id;
            }

            if (!$this->itemSwapRepo->employeesInSameBranchOrDivision($fromBranchId, $toBranchId)) {
                DB::rollBack();
                session()->flash('error', 'Swap is allowed only within the same branch or division.');

                return Redirect::back()->withInput();
            }

            $swappedCount = $this->itemSwapRepo->initiateSwap(
                $inventoryIds,
                $fromEmployeeId,
                $fromBranchId,
                $toEmployeeId,
                $toBranchId,
                $orgId,
                $remarks
            );

            if ($swappedCount > 0) {
                DB::commit();
                session()->flash('success', "Successfully initiated swap for {$swappedCount} item(s). Receiver must confirm from Receive Swap page.");

                return redirect()->route('swap-items', ['employee_id' => $fromEmployeeId]);
            }

            DB::rollBack();
            session()->flash('error', 'No items were swapped. Please check selected items.');

            return Redirect::back()->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());

            return Redirect::back()->withInput();
        }
    }

    public function receiveSwapItems(Request $request, $swapNo = null)
    {
        try {
            $userAccessData = $this->userAccessFunction();
            $swapNo = trim((string) ($request->input('swap_no') ?? $swapNo ?? ''));
            $employeeId = trim((string) $request->input('employee_id', ''));
            $pendingSwaps = collect();
            $fromEmployeeInfo = null;
            $toEmployeeInfo = null;

            if ($swapNo !== '') {
                $pendingSwaps = $this->itemSwapRepo->getPendingSwapsBySwapNo($swapNo);
            } elseif ($employeeId !== '') {
                $pendingSwaps = $this->itemSwapRepo->getPendingSwapsByEmployeeId($employeeId);
                if ($pendingSwaps->isNotEmpty()) {
                    $swapNo = (string) $pendingSwaps->first()->swap_no;
                }
            } elseif (auth()->user()->employee_id) {
                $employeeId = (string) auth()->user()->employee_id;
                $pendingSwaps = $this->itemSwapRepo->getPendingSwapsByEmployeeId($employeeId);
                if ($pendingSwaps->isNotEmpty()) {
                    $swapNo = (string) $pendingSwaps->first()->swap_no;
                }
            }

            if ($pendingSwaps->isEmpty() && ($swapNo !== '' || $employeeId !== '')) {
                session()->flash('error', 'No pending swap items found.');
            }

            if ($pendingSwaps->isNotEmpty()) {
                $first = $pendingSwaps->first();
                if (!empty($first->from_employee_id)) {
                    $fromEmployeeInfo = json_decode($this->employeeInfo('/api/v3/user-info-padmaportal?emp_id=' . $first->from_employee_id));
                }
                if (!empty($first->to_employee_id)) {
                    $toEmployeeInfo = json_decode($this->employeeInfo('/api/v3/user-info-padmaportal?emp_id=' . $first->to_employee_id));
                }
            }

            return view('backend.item.receive_swap_items')
                ->with('pendingSwaps', $pendingSwaps)
                ->with('swapNo', $swapNo)
                ->with('searchEmployeeId', $employeeId)
                ->with('fromEmployeeInfo', $fromEmployeeInfo)
                ->with('toEmployeeInfo', $toEmployeeInfo)
                ->with('user_access', $userAccessData);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return Redirect::back();
        }
    }

    public function saveReceiveSwapItems(Request $request)
    {
        $validation = $this->itemSwapRepo->checkRequestValidityForReceiveSwap($request);
        if (!$validation['isValidationSuccess']) {
            session()->flash('error', $validation['error']);

            return Redirect::back();
        }

        DB::beginTransaction();
        try {
            $swapNo = trim((string) $request->input('swap_no'));
            $swapLogIds = $request->input('swap_log_ids', []);
            $remarks = $request->input('remarks', []);

            if (empty($swapLogIds)) {
                session()->flash('error', 'Please select at least one item to receive.');

                return Redirect::back();
            }

            $updatedCount = $this->itemSwapRepo->receiveSwapItems($swapNo, $swapLogIds, $remarks);

            if ($updatedCount > 0) {
                DB::commit();
                session()->flash('success', "Successfully received {$updatedCount} swapped item(s)!");

                return redirect()->route('receive-swap-items', ['swap_no' => $swapNo]);
            }

            DB::rollBack();
            session()->flash('error', 'No items were received. Please verify you are the assigned receiver.');

            return Redirect::back();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());

            return Redirect::back();
        }
    }
}
