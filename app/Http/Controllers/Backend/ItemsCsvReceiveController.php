<?php

namespace App\Http\Controllers\Backend;

use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class ItemsCsvReceiveController extends BaseController
{
    use UserAccessTraits;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show pending CSV uploaded items for receive.
     */
    public function index(Request $request)
    {
        $userAccessData = $this->userAccessFunction();
        $orgId = auth()->user()->org_id ?? 1;
        $branchId = auth()->user()->branch_id ?? null;
        $searchSetUniqNo = trim((string)$request->input('set_uniq_no', ''));

        $query = DB::table('item_inventory as ii')
            ->leftJoin('item_info as item', 'ii.item_id', '=', 'item.id')
            ->leftJoin('category as cat', 'ii.category_id', '=', 'cat.id')
            ->leftJoin('brand as br', 'ii.brand_id', '=', 'br.id')
            ->select(
                'ii.id',
                'ii.set_uniq_no',
                'ii.stock_in_no',
                'ii.quantity',
                'ii.uniq_serial_no',
                'ii.condition',
                'ii.description',
                'ii.ram',
                'ii.storage',
                'ii.processor',
                'ii.created_date',
                'item.item_title',
                'cat.category_title',
                'br.brand_name'
            )
            ->where('ii.org_id', $orgId)
            ->where(function ($q) {
                $q->whereNull('ii.received_employee_id');
            })
            ->where('ii.status', 1)
            ->whereNotNull('ii.stock_in_no')
            ->orderByDesc('ii.id');

        if ($branchId !== null && $branchId !== '') {
            $query->where('ii.branch_id', $branchId);
        }

        if ($searchSetUniqNo !== '') {
            $query->where('ii.set_uniq_no', 'like', '%' . $searchSetUniqNo . '%');
        }

        $pendingItems = $query->limit(500)->get();

        return view('backend.items_csv_receive.index')
            ->with('pendingItems', $pendingItems)
            ->with('searchSetUniqNo', $searchSetUniqNo)
            ->with('user_access', $userAccessData);
    }

    /**
     * Save receive for selected CSV uploaded items.
     */
    public function store(Request $request)
    {
        $request->validate([
            'inventory_ids' => 'required|array|min:1',
            'inventory_ids.*' => 'required|integer',
        ], [
            'inventory_ids.required' => 'Please select at least one item.',
        ]);

        $orgId = auth()->user()->org_id ?? 1;
        $branchId = auth()->user()->branch_id ?? null;
        $inventoryIds = array_values(array_unique($request->input('inventory_ids', [])));
        $remarks = $request->input('receive_remarks', []);
        $receiveEmployeeBySet = $request->input('receive_employee_id_by_set', []);

        DB::beginTransaction();
        try {
            $query = DB::table('item_inventory')
                ->where('org_id', $orgId)
                ->whereIn('id', $inventoryIds)
                ->where('status', 1);

            if ($branchId !== null && $branchId !== '') {
                $query->where('branch_id', $branchId);
            }

            $eligibleRows = $query
                ->where(function ($q) {
                    $q->whereNull('received_employee_id');
                })
                ->select('id', 'set_uniq_no')
                ->get();

            $eligibleIds = $eligibleRows->pluck('id')->all();

            if (empty($eligibleIds)) {
                DB::rollBack();
                return Redirect::back()->with('eMessage', 'No eligible pending item found to receive.');
            }

            $updatedCount = 0;
            $missingEmployeeSets = [];
            foreach ($eligibleRows as $row) {
                $setUniqNo = trim((string)($row->set_uniq_no ?? ''));
                $receiverEmployeeId = trim((string)($receiveEmployeeBySet[$setUniqNo] ?? ''));

                if ($receiverEmployeeId === '') {
                    $missingEmployeeSets[] = $setUniqNo !== '' ? $setUniqNo : 'N/A';
                    continue;
                }

                DB::table('item_inventory')
                    ->where('id', $row->id)
                    ->update([
                        'received_employee_id' => $receiverEmployeeId,
                        'received_branch_id' => $branchId,
                        'receive_date_time' => now(),
                        'receive_remarks' => trim((string)($remarks[$row->id] ?? '')),
                    ]);
                $updatedCount++;
            }

            if (!empty($missingEmployeeSets)) {
                DB::rollBack();
                $setList = implode(', ', array_unique($missingEmployeeSets));
                return Redirect::back()->with('eMessage', "Receive Employee ID required for SET_UNIQ_NO: {$setList}");
            }

            DB::commit();
            return Redirect::route('items-inventory-csv-receive')
                ->with('success', "Successfully received {$updatedCount} item(s).");
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with('eMessage', 'Receive failed: ' . $e->getMessage());
        }
    }
}

