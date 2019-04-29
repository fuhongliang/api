<?php

namespace App\Http\Controllers;

use App\BModel;
use Illuminate\Support\Facades\Crypt;
use Overtrue\EasySms\EasySms;

class BaseController extends Controller
{
    /**
     * @param $store_id
     * @return bool
     */
    static function checkStoreExist($store_id)
    {
        $count = BModel::getCount('store', ['store_id' => $store_id]);
        return $count > 0 ? true : false;
    }

    /**
     * @param int $code
     * @param string $msg
     * @param null $data
     * @return \Illuminate\Http\JsonResponse
     */
    static function jsonReturn($code = 200, $msg = '', $data = null)
    {
        return response()->json([
            'code' => $code,
            'data' => empty($data) || !isset($data) ? null : $data,
            'msg' => $msg
        ]);
    }

    /**
     * @param $price
     * @return string
     */
    static function ncPriceFormat($price)
    {
        $price_format = number_format($price, 2, '.', '');
        return $price_format;
    }

    /**
     * @param $store_id
     * @param $member_name
     * @return mixed
     */
    static function makeToken($store_id, $member_name)
    {
        return Crypt::encryptString(serialize($store_id));
    }


}
