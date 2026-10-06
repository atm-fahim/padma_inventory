<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\UserRepo;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

/**
 * Repository implementation of user for the user model.
 * they are user info
 * @develop by fahim
 * @author Padma Bank limited
 * @since 2023-08-20
 */
class UserRepoImpl extends EloquentBaseRepository implements UserRepo
{
    protected $model;
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $id = $request->input('id');
        if(!empty($id)){
            $validStatus = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'userType' => ['required', 'string'],
            ]);
        }else {
            $validStatus = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'userType' => ['required', 'string'],
            ]);
        }
        return $validStatus->fails() ? array('isValidationSuccess' => false, 'error' => $validStatus->errors())
            : array('isValidationSuccess' => true);
    }

    public function existUserInfo($email){
        return $this->model::where('email', $email)->first();
    }
    public function instructorInfo(){
        $id = auth()->user()->id;
        if(!empty(auth()->user()->user_types) && auth()->user()->user_types=='instructor') {
            return $this->model::select('*')
                ->where('user_types', 'instructor')
                ->where('id', $id)
                ->get();
        }else{
            return $this->model::select('*')
                ->where('user_types', 'instructor')
                ->get();
        }
    }


}
