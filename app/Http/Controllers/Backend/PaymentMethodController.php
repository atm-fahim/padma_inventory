<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\PaymentMethodRepo;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class PaymentMethodController extends BaseController
{
    private $paymentMethodRepo;
    use UserAccessTraits;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PaymentMethodRepo $pmRepo)
    {
        $this->middleware('auth');
        $this->paymentMethodRepo = $pmRepo;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userAccessData     = $this->userAccessFunction();
        $paymentMethodData  = $this->paymentMethodRepo->withoutDeletingData();
        return view('backend.payment.payment_method')->with('payment_method', $paymentMethodData)
                                            ->with('user_access',$userAccessData);
    }

    public function saveUpdate(Request $request)
    {
        $data                           = array();
        $validation                     = $this->paymentMethodRepo->checkRequestValidity($request);
        if (!$validation["isValidationSuccess"]) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'error' => $validation["error"]], 400);
        }
        try {
            $id                          = $request->input('id');
            $data['method_name']         = $request->input('payment_method');
            $data['slug']                = Str::slug($data['method_name']);
            (!empty($id))?$this->paymentMethodRepo->update($id,$data):$this->paymentMethodRepo->save($data);
            return Redirect::to('payment-method');
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function edit($id)
    {
      $paymentMethodData=$this->paymentMethodRepo->findById($id);
      echo json_encode($paymentMethodData);
    }

    public function active($id)
    {
        $this->paymentMethodRepo->update($id,['status'=>1]);
        return redirect('payment-method');
    }

    public function inActive($id)
    {
        $this->paymentMethodRepo->update($id,['status'=>0]);
        return redirect('payment-method');
    }

    public function delete($id)
    {
        $this->paymentMethodRepo->update($id,['status'=>9]);
        return redirect('payment-method');
    }
}
