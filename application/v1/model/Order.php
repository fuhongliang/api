<?php
namespace app\v1\model;
use think\Model;
use think\Db;
use app\v1\model\Member as MemberModel;
use app\v1\model\Goods as GoodsModel;
/**
 * Class Order 订单模型
 * @package app\v1\model
 */
class Order extends Model{
    protected $pk = 'order_id';

    /**
     * @param array $condition
     * @param array $extend
     * @param string $fields
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getNewOrder($condition = array(), $extend = array(), $fields = '*') {
        $order_info = Db::name('order')->field($fields)->where($condition)->select();
        if (empty($order_info)) {
            return array();
        }
        foreach ($order_info as &$data) {
            $total_price=$commis_price=$goods_pay_price=0;
            if (in_array('order_common', $extend)) {
                $order_common = self::getOrderCommonInfo(array('order_id' => $data['order_id']));
                $reciver_info = unserialize($order_common['reciver_info']);
                $data['extend_order_common']['phone']=$reciver_info['phone'];
                $data['extend_order_common']['address']=$reciver_info['address'];
                $data['extend_order_common']['reciver_name']=$order_common['reciver_name'];
            }
            if (in_array('order_goods', $extend)) {
                //取商品列表
                $params='goods_name,goods_price,goods_num,commis_rate,goods_pay_price';
                $order_goods_list = self::getOrderGoodsList(array('order_id' => $data['order_id']),$params);
                $data['extend_order_goods'] = $order_goods_list;
            }
            foreach ($order_goods_list as $v)
            {
                $total_price += $v['goods_price']*$v['goods_num'];
                $commis_price +=$v['goods_price']*$v['goods_num']*$v['commis_rate'];
                $goods_pay_price +=$v['goods_pay_price'];
            }
            $data['add_time']=date('Y-m-d H:i:s',$data['add_time']);
            $data['total_price']=$total_price;
            $data['commis_price']=$commis_price;
            $data['goods_pay_price']=$goods_pay_price;
            unset($data);
        }
        return $order_info;
    }

    /**
     * @param array $condition
     * @param string $fields
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getOrderCommonInfo($condition = array(), $fields = '*') {
        return Db::name('order_common')->field($fields)->where($condition)->find();
    }

    /**
     * @param array $condition
     * @param string $fields
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getOrderGoodsList($condition = array(), $fields = '*') {
        return Db::name('order_goods')->field($fields)->where($condition)->select();
    }

    /**
     * @param $condition
     * @param string $field
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getOrderInfo($condition, $field = '*')
    {
        return Db::name('order')->field($field)->where($condition)->find();
    }

    /**
     * @param $order_id
     * @param $refuse_reason
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function cancelOrder($order_id,$refuse_reason)
    {
        $fields='goods_id,goods_num';
        $goods_list = self::getOrderGoodsList(array('order_id'=>$order_id),$fields);
        Db::transaction(function () use ($order_id,$refuse_reason,$goods_list){
            self::editOrder(['order_id'=>$order_id],['order_state'=>0,'refuse_reason'=>$refuse_reason]);
            foreach($goods_list as $v)
            {
                Db::name('goods')
                    ->where('goods_id',$v['goods_id'])
                    ->inc('goods_storage',$v['goods_num'])
                    ->dec('goods_salenum',$v['goods_num'])
                    ->update();
            }
        });
        return true;
    }
    /**
     * @param $condition
     * @param $up_data
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static function editOrder($condition,$up_data)
    {
        return Db::name('order')
            ->where($condition)
            ->update($up_data);
    }



}