<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController as Base;
use App\model\V1\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Base
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function getNewOrder(Request $request)
    {
        $store_id=$request->input('store_id');
        if(!$store_id)
        {
            return Base::jsonReturn(1000,'参数缺失');
        }
        $fileds=['order_id','order_sn','buyer_id','add_time'];
        $info=Order::getNewOrder(['store_id'=>$store_id,'order_state'=>20],$fileds,20);
        return Base::jsonReturn(200,'获取成功',$info);

    }
    public function refuseOrder(Request $request)
    {
        $order_id=$request->input('order_id');
        $refuse_reason=$request->input('refuse_reason');
        if(!$order_id || !$refuse_reason)
        {
            return Base::jsonReturn(1000,'参数缺失');
        }
        $res=Order::cancelOrder($order_id,$refuse_reason);
        if($res)
        {
            return Base::jsonReturn(200,'拒单成功');
        }else{
            return Base::jsonReturn(2000,'拒单失败');
        }
    }
    public function receiveOrder(Request $request)
    {
        $order_id=$request->input('order_id');
        if(!$order_id)
        {
            return Base::jsonReturn(1000,'参数缺失');
        }
        $res=Order::editOrder(['order_id'=>$order_id],['order_state'=>25]);
        if($res)
        {
            return Base::jsonReturn(200,'接单成功');
        }else{
            return Base::jsonReturn(2000,'接单失败');
        }

    }
    public function getOrderList(Request $request)
    {
        //order_state  20新订单  30已发货  40 已收货   0已取消
        $order_state=$request->input('order_state');
        $store_id=$request->input('store_id');
        if(!$store_id)
        {
            return Base::jsonReturn(1000,'参数缺失');
        }
        $fileds=['order_id','order_sn','buyer_id','add_time'];
        $info=Order::getNewOrder(['store_id'=>$store_id,'order_state'=>$order_state],$fileds,$order_state);
        return Base::jsonReturn(200,'获取成功',$info);
    }
}
