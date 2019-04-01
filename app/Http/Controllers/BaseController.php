<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
    static function makeToken($member_name,$member_pwd)
    {
        $secret_key=md5(date('y-m-d h:i:s',time()).microtime());
        return md5($member_name.$member_pwd.$secret_key);
    }


}
