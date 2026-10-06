<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Traits implementation of menu for the backend model.
 * they are user access info
 * @develop by fahim
 * @author Padma Bank limited
 * @since 2024-02-04
 */
trait CommonTraits
{
    public function postApiResponse($url, $body, $header)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $header,
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
    public function postApiCareerResponse($url, $body, $header)
    {
        $curl = curl_init();
            curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_SSLCERT => base_path() . '/ssl/star_padmabankbd_com.crt',
//            CURLOPT_SSLKEY  => base_path() . '/ssl/star_padmabankbd_com.key',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $header,
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    public function getApiResponse($url, $body, $header)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $header,
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    public function ldap_info($username, $ldap)
    {
        $filter = "(sAMAccountName=$username)";
        $result = ldap_search($ldap, "dc=padmabankbd,dc=COM", $filter);
        $info = ldap_get_entries($ldap, $result);
        $jsonData = json_encode($info);
        $divisionBranchName = $this->getAdBranchName($info[0]["distinguishedname"][0]);
        $impInfo = json_decode($this->employeeInfo($info[0]["description"][0]));
        //echo $impInfo->branch_id;exit;
        // Output JSON data
        $data = array(
            'employee_id' => $info[0]["description"][0],
            'user_name' => $info[0]["samaccountname"][0],
            'user_full_name' => $info[0]["displayname"][0],
            'user_email' => $info[0]["userprincipalname"][0],
            'branch_id' => (!empty($impInfo->branch_id))?$impInfo->branch_id:0,
            'branch_div_name' => (!empty($impInfo->branch_name))?$impInfo->branch_name:'N/A',
            'phone_number' => (!empty($info[0]["telephonenumber"][0]))?$info[0]["telephonenumber"][0]:'',
            'status' => 'ok',
        );
        return $data;
    }

    public function employeeInfo($endPointUrl){
        return $this->employeeInfoByToken($this->getEmpToken(),$endPointUrl);
    }

    private function getEmpToken(){
        $url    = env('PADMA_HRM_API_URL')."/api/login";
        $header = array(
            "Content-Type" => "application/json"
        );
        $body = array(
            "user_name" => "pbl_lms",
            "password"=>"123pbl!@#"
        );
        $getData = $this->postApiResponse($url,$body,$header);
        return json_decode($getData)->access_token;
    }

    private function employeeInfoByToken($token,$endPointUrl){
        $url    = env('PADMA_HRM_API_URL').$endPointUrl;
        $header = [
            "Authorization: bearer ".$token
        ];
        $body = "";
        return $this->getApiResponse($url,$body,$header);
    }

    private function getAdBranchName($input){
 // Split the string by commas
        $parts = explode(',', $input);
        $division = explode('=', $parts[2]);
        return $division[1];
    }
    function encrypt($msg, $key)
    {
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);

        $ciphertext_raw = openssl_encrypt($msg, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
        $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);

        return $ciphertext;

    }

    function decrypt($ciphermsg, $key)
    {
        $c = base64_decode($ciphermsg);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);

        if (function_exists('hash_equals')) {
            if (hash_equals($hmac, $calcmac)) return $original_plaintext;
        } else {
            if ($this->hash_equals_custom($hmac, $calcmac)) return $original_plaintext;
        }

    }

}
