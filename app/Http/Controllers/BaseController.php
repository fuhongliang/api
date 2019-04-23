<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Overtrue\EasySms\EasySms;
class BaseController extends Controller
{
    /**
     * @param int $code
     * @param string $msg
     * @param null $data
     * @return \Illuminate\Http\JsonResponse
     */
    static function jsonReturn($code=200,$msg='',$data=null)
    {
        return response()->json([
            'code'=>$code,
            'data'=>empty($data)|| !isset($data)? null :$data,
            'msg'=>$msg
        ]);
    }

    /**
     * @param $price
     * @return string
     */
    static function ncPriceFormat($price) {
        $price_format   = number_format($price,2,'.','');
        return $price_format;
    }

    /**
     * @param $store_id
     * @param $member_name
     * @return mixed
     */
    static function makeToken($store_id,$member_name)
    {
        return Crypt::encryptString(serialize($store_id));
    }



}
