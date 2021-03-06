<?php

namespace App\model\V3;

use App\BModel;
use App\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends BModel
{

    static function getNewOrder($condition, $fields = ['*'])
    {
        $order_info = DB::table('order')->where($condition)->orderBy('payment_time','desc')->get($fields);
        if (empty($order_info)) {
            return array();
        }
        foreach ($order_info as &$data) {
            $total_price  = $commis_price = $goods_pay_price = 0;
            $order_common = self::getOrderCommonInfo(array('order_id' => $data->order_id));
            $reciver_info = unserialize($order_common->reciver_info);
            //  dd($reciver_info);
            $data->extend_order_common['phone']        = $reciver_info['phone'];
            $data->extend_order_common['address']      = $reciver_info['address'];
            $data->extend_order_common['reciver_name'] = $order_common->reciver_name;

            //取商品列表
            $params                   = ['goods_name', 'goods_price', 'goods_num', 'commis_rate', 'goods_pay_price'];
            $order_goods_list         = self::getOrderGoodsList(array('order_id' => $data->order_id), $params);
            $data->extend_order_goods = $order_goods_list;
            foreach ($order_goods_list as $v) {
                $total_price  += $v->goods_pay_price * $v->goods_num;
                $commis_price += $v->goods_pay_price * $v->goods_num * ($v->commis_rate / 100);
            }

            $data->delivery['name']  = "三爷";
            $data->delivery['phone'] = "13124154747";
            $data->order_state       = "配送中";

            $data->add_time        = date('Y-m-d H:i:s', $data->add_time);
            $data->total_price     = $total_price;
            $data->commis_price    = ROUND($commis_price, 2);
            $data->goods_pay_price = $total_price - $commis_price;
            unset($data);
        }


        return $order_info;
    }

    static function getOrderList($condition, $fields = ['*'], $order_state, $page)
    {
        $result = [];
        $skip   = ($page - 1) * 10;
        if ($order_state == 25) {
            $count = DB::table('order')
                ->where($condition)
                ->whereIn('order_state', [25, 30, 35])
                ->count();
            $total = intval(ceil($count / 10));
            if ($page > $total) {
                return $result;
            }
            $order_info = DB::table('order')
                ->where($condition)
                ->whereIn('order_state', [25, 30, 35])
                ->orderBy('order_id', 'desc')
                ->skip($skip)
                ->take(10)
                ->get($fields);
        } else {
            $count = DB::table('order')
                ->where($condition)
                ->where('order_state', $order_state)
                ->count();
            $total = intval(ceil($count / 10));
            if ($page > $total) {
                return $result;
            }
            $order_info = DB::table('order')
                ->where($condition)
                ->where('order_state', $order_state)
                ->orderBy('order_id', 'desc')
                ->skip($skip)
                ->take(10)
                ->get($fields);
        }

        if (empty($order_info)) {
            return array();
        }

        foreach ($order_info as &$data) {
            $total_price  = $commis_price = $goods_pay_price = 0;
            $order_common = self::getOrderCommonInfo(array('order_id' => $data->order_id));
            $reciver_info = unserialize($order_common->reciver_info);
            //  dd($reciver_info);
            $data->extend_order_common['phone']        = $reciver_info['phone'];
            $data->extend_order_common['address']      = $reciver_info['address'];
            $data->extend_order_common['reciver_name'] = $order_common->reciver_name;

            //取商品列表
            $params           = ['goods_name', 'goods_price', 'goods_num', 'commis_rate', 'goods_pay_price'];
            $order_goods_list = self::getOrderGoodsList(array('order_id' => $data->order_id), $params);

            $data->extend_order_goods = $order_goods_list;
            foreach ($order_goods_list as $v) {
                $total_price  += $v->goods_pay_price;
                $commis_price += BaseController::ncPriceFormat(BaseController::ncPriceFormat($v->goods_pay_price) * $v->goods_num * (intval($v->commis_rate) / 100));
            }

            $data->delivery['name']  = "三爷";
            $data->delivery['phone'] = "13124154747";
            if ($order_state == 25) {
                $data->order_state = "配送中";
            }
            if ($order_state == 0) {
                $data->order_state = "已取消";
            }
            if ($order_state == 40) {
                $data->order_state = "已完成";
            }
            $data->add_time        = date('Y-m-d H:i:s', $data->add_time);
            $data->total_price     = BaseController::ncPriceFormat($total_price);
            $data->commis_price    = BaseController::ncPriceFormat($commis_price);
            $data->goods_pay_price = BaseController::ncPriceFormat($total_price - $commis_price);
            unset($data);
        }
        return $order_info;
    }

    /**
     * @param $condition
     * @param array $fields
     * @return Model|\Illuminate\Database\Query\Builder|object|null
     */
    static function getOrderCommonInfo($condition, $fields = ['*'])
    {
        return DB::table('order_common')->where($condition)->first($fields);
    }

    /**
     * @param $condition
     * @param array $fields
     * @return \Illuminate\Support\Collection
     */
    static function getOrderGoodsList($condition, $fields = ['*'])
    {
        return BModel::getTableAllData('order_goods', $condition, $fields);
    }

    /**
     * @param $order_id
     * @param $refuse_reason
     * @return bool
     */
    static function cancelOrder($order_id, $refuse_reason)
    {
        $fields     = ['goods_id', 'goods_num'];
        $goods_list = self::getOrderGoodsList(['order_id' => $order_id], $fields);
        self::editOrder(['order_id' => $order_id], ['order_state' => 0, 'refuse_reason' => $refuse_reason]);
        foreach ($goods_list as $v) {
            DB::table('goods')
                ->where('goods_id', $v->goods_id)
                ->increment('goods_storage', intval($v->goods_num));
            DB::table('goods')
                ->where('goods_id', $v->goods_id)
                ->decrement('goods_storage', intval($v->goods_num));
        }
        return true;
    }

    /**
     * @param $condition
     * @param $up_data
     * @return int
     */
    static function editOrder($condition, $up_data)
    {
        return BModel::upTableData('order', $condition, $up_data);
    }


}
