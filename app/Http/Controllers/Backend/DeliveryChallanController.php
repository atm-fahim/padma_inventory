<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Interfaces\Repo\Backend\DeliveryChallanRepo;
use App\Interfaces\Repo\Backend\ItemsRepo;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryChallanController extends Controller
{
    use UserAccessTraits;

    private DeliveryChallanRepo $deliveryChallanRepo;
    private ItemsRepo $itemsRepo;

    public function __construct(DeliveryChallanRepo $deliveryChallanRepo, ItemsRepo $itemsRepo)
    {
        $this->middleware('auth');
        $this->deliveryChallanRepo = $deliveryChallanRepo;
        $this->itemsRepo = $itemsRepo;
    }

    public function index()
    {
        $userAccessData = $this->userAccessFunction();
        $orgId = Auth::user()->org_id ?? 1;
        $items = $this->itemsRepo->withoutDeletingData();
        return view('backend.item.delivery_challan')
            ->with('itemsData', $items)
            ->with('user_access', $userAccessData);
    }

    public function store(Request $request)
    {
        $validation = $this->deliveryChallanRepo->checkRequestValidity($request);
        if (!$validation['isValidationSuccess']) {
            return back()
                ->withErrors($validation['error'])
                ->withInput();
        }

        $orgId = Auth::user()->org_id ?? 1;
        $branchId = Auth::user()->branch_id ?? null;

        $challanData = [
            'org_id'           => $orgId,
            'branch_id'        => $branchId,
            'challan_no'       => $this->deliveryChallanRepo->generateChallanNo(),
            'challan_date'     => $request->input('challan_date'),
            'buyer_name'       => $request->input('buyer_name'),
            'buyer_address'    => $request->input('buyer_address'),
            'delivery_location'=> $request->input('delivery_location'),
            'reference_no'     => $request->input('reference_no'),
            'status'           => 1,
        ];

        $itemsData = [];
        $itemNames   = $request->input('item_name', []);
        $descriptions = $request->input('description', []);
        $quantities  = $request->input('quantity', []);
        $uoms        = $request->input('uom', []);
        $warranties  = $request->input('warranty', []);

        foreach ($itemNames as $index => $name) {
            if (empty($name)) {
                continue;
            }

            $itemsData[] = [
                'item_name'   => $name,
                'description' => $descriptions[$index] ?? null,
                'quantity'    => $quantities[$index] ?? 1,
                'uom'         => $uoms[$index] ?? null,
                'warranty'    => $warranties[$index] ?? null,
                'status'      => 1,
            ];
        }

        $challan = $this->deliveryChallanRepo->createWithItems($challanData, $itemsData);

        return redirect()
            ->route('delivery-challan-show', $challan->id);
    }

    public function show($id)
    {
        $challan = $this->deliveryChallanRepo->findWithItemsById($id);
        return view('backend.item.delivery_challan_invoice')
            ->with('challan', $challan);
    }
}

