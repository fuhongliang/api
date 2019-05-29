<?php

namespace App\Http\Controllers;

use App\BModel;
use App\Http\Controllers\BaseController as Base;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redis;


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
            'code' => intval($code),
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
    static function makeToken($member_name)
    {
        return Crypt::encryptString(serialize($member_name));
    }

    /**
     * 生成支付单编号(两位随机 + 从2000-01-01 00:00:00 到现在的秒数+微秒+会员ID%1000)，该值会传给第三方支付接口
     * 长度 =2位 + 10位 + 3位 + 3位  = 18位
     * 1000个会员同一微秒提订单，重复机率为1/100
     * @return string
     */
    static function makePaySn($member_id)
    {
        return mt_rand(10, 99)
            . sprintf('%010d', time() - 946656000)
            . sprintf('%03d', (float)microtime() * 1000)
            . sprintf('%03d', (int)$member_id % 1000);
    }
    /**
     * 订单编号生成规则，n(n>=1)个订单表对应一个支付表，
     * 生成订单编号(年取1位 + $pay_id取13位 + 第N个子订单取2位)
     * 1000个会员同一微秒提订单，重复机率为1/100
     * @param $pay_id 支付表自增ID
     * @return string
     */
    static function makeOrderSn($pay_id)
    {
        //记录生成子订单的个数，如果生成多个子订单，该值会累加
        static $num;
        if (empty($num)) {
            $num = 1;
        } else {
            $num++;
        }
        return (date('y', time()) % 9 + 1) . sprintf('%013d', $pay_id) . sprintf('%02d', $num);
    }

}
