<?php
namespace app\v1\controller;
use app\v1\controller\Base;
use app\v1\model\Order as OrderModel;
use think\Request;

/**
 * Class Order  订单
 * @package app\v1\controller
 */
class Order extends Base
{
    /** 获取新订单
     * @param Request $request
     * @return array
     */
    public function newOrder(Request $request)
    {
        $store_id=$request->param('store_id');
        if(!$store_id)
        {
            return Base::jsonReturn(1000,[],'参数缺失');
        }
        $fileds='order_id,order_sn,buyer_id,add_time';
        $info=OrderModel::getNewOrder(['store_id'=>$store_id,'order_state'=>20],array('order_goods','order_common'),$fileds);
        return Base::jsonReturn(200,$info,'获取成功');
    }

    /** 商家拒单
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function refuseOrder(Request $request)
    {
        $order_id=$request->param('order_id');
        $refuse_reason=$request->param('refuse_reason');
        if(!$order_id || !$refuse_reason)
        {
            return Base::jsonReturn(1000,[],'参数缺失');
        }
        $res=OrderModel::cancelOrder($order_id,$refuse_reason);
        if($res)
        {
            return Base::jsonReturn(200,[],'拒单成功');
        }else{
            return Base::jsonReturn(2000,[],'拒单失败');
        }
    }

    /**  根据状态获取订单列表
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderList(Request $request)
    {
        //order_state  20新订单  30已发货  40 已收货   0已取消
        $order_state=$request->param('order_state');
        $store_id=$request->param('store_id');
        $fileds='order_id,order_sn,buyer_id,add_time';
        $info=OrderModel::getNewOrder(['store_id'=>$store_id,'order_state'=>$order_state],array('order_goods','order_common'),$fileds);
        return Base::jsonReturn(200,$info,'获取成功');
    }

}