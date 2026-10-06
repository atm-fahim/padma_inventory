<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\PaymentMethodRepo;
use App\Models\PaymentMethodModel;
use Illuminate\Support\Facades\Validator;

/**
 * Repository implementation of bank mfs for the bank mfs model.
 * they are bank mfs info
 * @develop by fahim
 * @author Padma Bank limited
 * @since 2023-08-20-04-15
 */
class PaymentMethodRepoImpl extends EloquentBaseRepository implements PaymentMethodRepo
{
    protected $model;

    public function __construct(PaymentMethodModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'payment_method' => 'required|string',
        ]);
        return $validStatus->fails() ? array('isValidationSuccess' => false, 'error' => $validStatus->errors())
            : array('isValidationSuccess' => true);
    }


}
