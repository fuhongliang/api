<?php
namespace app\v1\model;
use think\Model;
use think\DB;
/**
 * Class Member
 * @package app\v1\model 商家模型
 */
class Store extends Model{
    protected $pk = 'store_id';

    /** 获取商家店铺详情
     * @param $condition
     * @param string $field
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getStoreInfo($condition, $field = '*')
    {
        return Db::name('store')->field($field)->where($condition)->find();
    }

    /** 获取店铺以及申请信息
     * @param string $field
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function  getStoreAndJoinInfo($field = '*')
    {
        return Db::name('store')
            ->alias('a')
            ->join('store_joinin b','a.member_id = b.member_id')
            ->field($field)
            ->find();
    }

    /**
     * @param $ins_data
     * @return int|string
     */
    static function addStoreGoodsClass($ins_data)
    {
        return Db::name('store_goods_class')->insert($ins_data);
    }

    /**
     * @param $store_id
     * @param $class_name
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function checkStoreGoodsClassExist($store_id,$class_name)
    {
        $data=Db::name('store_goods_class')
            ->where('store_id',$store_id)
            ->where('stc_name',$class_name)
            ->find();
        return empty($data)?true:false;
    }



}
