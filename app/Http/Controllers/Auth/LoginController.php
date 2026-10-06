<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Interfaces\Repo\Backend\BranchRepo;
use App\Interfaces\Repo\Backend\UserRepo;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use App\Traits\CommonTraits;
use App\Traits\UserAccessTraits;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    use CommonTraits;
    use UserAccessTraits;
    private $userRepo;
    private $brRepo;
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRepo $userRepo,BranchRepo $brRepo)
    {
        $this->userRepo = $userRepo;
        $this->brRepo = $brRepo;
        $this->middleware('web')->except('logout');
    }

    public function login(Request $request){
        $ldapInfo = $this->adauth($request->email,$request->password);
        if(isset($ldapInfo['status']) && !empty($ldapInfo['status']) && $ldapInfo['status']=='ok') {
            $existInfo = $this->userRepo->existUserInfo($ldapInfo['user_email']);
            if(empty($existInfo)){
                $this->createStudentAdUser($ldapInfo,$request->password,$id=null,$user_types=null);
            }else{
                $this->createStudentAdUser($ldapInfo,$request->password,$existInfo['id'],$existInfo['user_types']);
            }
            // Re-fetch after create/update so first-time login checks current status.
            $currentUserInfo = $this->userRepo->existUserInfo($ldapInfo['user_email']);
            if(isset($currentUserInfo['status']) && (int)$currentUserInfo['status'] === 1) {
                if (Auth::attempt(['email' => $ldapInfo['user_email'], 'password' => $request->password])) {
                    try {
                        return Redirect::to('dashboard');
                    } catch (Exception $e) {
                        return Redirect::to('login')->withFail('You have entered an invalid username or password..!');
                    }
                } else {
                    return Redirect::to('login')->withFail('You have entered an invalid username or password..!');
                }
            }else{
                return Redirect::to('login')->withFail('Your User disabled..!');
            }
        }else{
            return Redirect::to('login')->withFail('You have entered an invalid username or password..!');
        }
    }

    public function adauth($ad_name, $ad_password)
    {
        $adServer = "192.168.200.36";
        $ldap = ldap_connect($adServer);

        $username = $ad_name;
        $password = $ad_password;
        $ldaprdn = 'padma\\' . $username;
        try {
            ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
            if(@ldap_bind($ldap, $ldaprdn, $password)){
                return $this->ldap_info($username,$ldap);
            }else{
                return 'You have entered an invalid username or password..!';
            }
        } catch (Exception $e) {
            return 'You have entered an invalid username or password..!';
        }
    }
    private function createStudentAdUser($ldapInfo,$password,$id,$user_types)
    {

        $data = array();
        //branch_div_name
        try {
            if (!empty($ldapInfo['employee_id'])) {
                $endPointUrl = "/api/v3/user-info-padmaportal?emp_id=" . $ldapInfo['employee_id'];
                $fromEmployeeInfo = json_decode($this->employeeInfo($endPointUrl));
            }


            $data['employee_id'] = $ldapInfo['employee_id'];
            $data['name'] = $ldapInfo['user_name'];
            $data['email'] = $ldapInfo['user_email'];
            $data['branch_id'] = $fromEmployeeInfo->branch_id??0;
            $data['designation'] = $fromEmployeeInfo->emp_designation;
            $data['user_types']     = (!empty($user_types) && $user_types=='super-admin')?'super-admin':'ad-user';
            $data['contact_no'] = $ldapInfo['phone_number'];
            $data['password'] = Hash::make($password);

            (!empty($id)) ? $this->userRepo->update($id, $data) : $this->userRepo->save($data);
            return 1;
        } catch (\Exception $e) {
            return null;
        }
    }
    public function logout(Request $request)
    {
        $this->guard('web')->logout();
        $request->session()->invalidate();
        return $this->loggedOut($request) ?: redirect('/login');
    }
}

