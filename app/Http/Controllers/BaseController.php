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
    static function ncPriceFormat($price) {
        $price_format   = number_format($price,2,'.','');
        return $price_format;
    }
    static function makeToken($store_id,$member_name)
    {
        return Crypt::encryptString(serialize($store_id));
    }
    static function getSysSetPath(){
        switch(getenv('IMAGE_DIR_TYPE')){
            case "1":

                //按文件类型存放,例如/a.jpg

                $subpath = "";

                break;

            case "2":

                //按上传年份存放,例如2011/a.jpg

                $subpath = date("Y",time()) . "/";

                break;

            case "3":

                //按上传年月存放,例如2011/04/a.jpg

                $subpath = date("Y",time()) . "/" . date("m",time()) . "/";

                break;

            case "4":

                //按上传年月日存放,例如2011/04/19/a.jpg

                $subpath = date("Y",time()) . "/" . date("m",time()) . "/" . date("d",time()) . "/";

        }
        return $subpath;
    }


}
