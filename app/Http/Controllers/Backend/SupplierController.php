<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\SupplierRepo;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class SupplierController extends BaseController
{
    private $suppRepo;
    use UserAccessTraits;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SupplierRepo $suppRepo)
    {
        $this->middleware('auth');
        $this->suppRepo = $suppRepo;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userAccessData = $this->userAccessFunction();
        $supplierData = $this->suppRepo->supplierInfoByOrgId(auth()->user()->org_id??1);

        return view('backend.supplier.supplier')->with('supplier', $supplierData)
                                                    ->with('user_access',$userAccessData);
    }

    public function saveUpdate(Request $request)
    {

        $data = array();
        $validation = $this->suppRepo->checkRequestValidity($request);
        if (!$validation["isValidationSuccess"]) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'error' => $validation["error"]], 400);
        }
        try {
            $id                     = $request->input('id');
            $code                   = $request->input('code');
            $data['supplier_name']  = $request->input('supplier_name');
            $data['branch_id']      = auth()->user()->branch_id;
            $data['address']        = $request->input('address');
            $data['phone_number']   = $request->input('phone_number');
            $data['tin']            = $request->input('tin');
            $data['vat']            = $request->input('vat');
            $data['account_no']     = $request->input('account_no');
            $data['trade_license']  = $request->input('trade_license');
            $data['slug']           = Str::slug($data['supplier_name']);
            $data['code']           = (!empty($code))?$code:$this->generateRandomString($data['supplier_name']);
            (!empty($id)) ? $this->suppRepo->update($id, $data) : $this->suppRepo->save($data);
            return Redirect::to('supplier');
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function edit($id)
    {
        $supplierData = $this->suppRepo->findById($id);
        echo json_encode($supplierData);
    }

    public function active($id)
    {
        $this->suppRepo->update($id, ['status' => 1]);
        return redirect('supplier');
    }

    public function inActive($id)
    {
        $this->suppRepo->update($id, ['status' => 0]);
        return redirect('supplier');
    }

    public function delete($id)
    {
        $this->suppRepo->update($id, ['status' => 9]);
        return redirect('supplier');
    }
}
