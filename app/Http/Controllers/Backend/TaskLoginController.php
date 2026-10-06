<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;

class TaskLoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */



    public function index()
    {
        // usage
        $employeeId = '20230813001';
        $secret = 'inv'; // monitor app er

        $token = $this->makeSsoToken($employeeId, $secret);

        $loginUrl = 'https://noc.padmabankbd.com/auth/sso/employee?token=' . rawurlencode($token);
        return redirect()->away($loginUrl);
//        echo "<script>
//            window.open('$loginUrl', '_blank');
//            window.location.href = '/dashboard';
//         </script>";
//        exit;
    }


    function makeSsoToken(string $employeeId, string $secret): string
    {
        $cipher = 'AES-128-CBC';
        $key = substr(hash('sha256', $secret, true), 0, 16);

        // শুধু employee_id পাঠাতে চাইলে plain string
        $plaintext = $employeeId;

        $ivLen = openssl_cipher_iv_length($cipher);
        $iv = random_bytes($ivLen);

        $ciphertextRaw = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($ciphertextRaw === false) {
            throw new RuntimeException('Encryption failed');
        }

        $hmac = hash_hmac('sha256', $ciphertextRaw, $key, true);
        return base64_encode($iv . $hmac . $ciphertextRaw);
    }



}
