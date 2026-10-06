<?php

namespace App\Traits;


use Illuminate\Support\Facades\DB;
/**
 * Traits implementation of menu for the backend model.
 * they are user access info
 * @develop by fahim
 * @author Padma Bank limited
 * @since 2023-08-20-08-20
 */
trait UserAccessTraits
{
    public function userAccessFunction($id = null)
    {
        $uriSegments = explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        if($_SERVER['SERVER_NAME']=='172.20.11.44'){
            $menuSlug = $uriSegments[3];
        }else{
            $menuSlug = $uriSegments[1];
        }

//        echo $menuSlug;exit;
        if (!empty(auth()->user()->user_types)) {
            $user_type = auth()->user()->user_types;
//            $id = auth()->user()->id;
            $getMenuId = DB::table('backend_menu')->where('slug', $menuSlug)->first();
            $menuId = (!empty($getMenuId) && !empty($getMenuId->id)) ? $getMenuId->id : $id;
            $accessData = $menuId ? DB::table('access_menu')->where('menu_id', $menuId)->where('user_type', $user_type)->first() : null;
            return $accessData ?? (object)['view' => 1, 'add' => 1, 'edit' => 1, 'delete' => 0];
        } else {
            return 0;
        }
    }

    public function generateRandomString($supplier_name, $length = 4) : string
    {
        $spName = strtoupper(mb_substr($supplier_name, 0, 2));
        $characters = '0123456789ABCDEFGHI';
//        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $spName . '-' . $randomString;
    }

    public function generateRandomItemCode($branchCode, $categoryCode, $lastCode): string
    {
        //dd($branchCode, $categoryCode, $lastCode);
        $spName = 'PBL';//strtoupper(mb_substr($supplierName, 0, 2));
        $branchCode = strtoupper(mb_substr($branchCode, 0, 2));
        $categoryCode = strtoupper(mb_substr($categoryCode, 0, 2));
        //$code=explode("-",$lastCode);
        $code = !empty($lastCode) ? $lastCode + 1 : 5000;
        return $spName . $branchCode . $categoryCode . '-' . $code;
    }

    public function random_token(): string
    {
        $y = date("Y");
        $d = date("d");
        $m = date("m");
        $h = date("HsN");
        $yr = substr($y, -2);
        $invoiceno = $yr . $m . $d . '0000';
        return $invoiceno;
    }

}
