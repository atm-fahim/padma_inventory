<?php
namespace App\Http\Controllers;
use Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Models\User;
//https://www.positronx.io/laravel-9-socialite-login-with-google-example-tutorial/
class GoogleController extends Controller
{
    public function signInwithGoogle()
    {
        return Socialite::driver('google')->redirect();
    }
    public function callbackToGoogle()
    {
        try {

            $user = Socialite::driver('google')->user();

            $finduser = User::where('gauth_id', $user->id)->first();
            if($finduser){

                Auth::login($finduser);

                return redirect('/login');

            }else{
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'gauth_id'=> $user->id,
                    'branch_id'=> 1,
                    'gauth_type'=> 'google',
                    'user_types'=> 'google',
                    'password' => Hash::make('admin@123')
                ]);

                Auth::login($newUser);

                return redirect('/home');
            }

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
