<?php
namespace app\v1\model;
use think\Model;
use think\Db;
/**
 * Class Member
 * @package app\v1\model 商品模型
 */
class Goods extends Model
{
    /**
     * @param $condition
     * @param string $field
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getGoodsInfo($condition, $field = '*')
    {
        return Db::name('goods')->field($field)->where($condition)->find();
    }
    /**
     * @param $data
     * @return int|string
     */
    static function addGoodsCommon($data)
    {
        return  Db::name('goods_common')->insertGetId($data);
    }

    /**
     * @param $data
     * @return int|string
     */
    static function addGoods($data)
    {
        return  Db::name('goods')->insertGetId($data);
    }

    /**
     * @param $condition
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static function delGoodsCommon($condition)
    {
        return  Db::name('goods_common')->where($condition)->delete();
    }

    /**
     * @param $condition
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static function delGoodsById($condition)
    {
        return  Db::name('goods')->where($condition)->delete();
    }

    /**
     * @param $store_id
     * @param $goods_id
     * @return bool
     */
    static function delGoods($store_id,$goods_id)
    {
        Db::transaction(function () use ($goods_id){
            $data= self::getGoodsInfo(['goods_id'=>$goods_id],['goods_commonid']);
            $where=array();
            $where['goods_lock']=0;
            $where['goods_commonid']=$data['goods_commonid'];
            self::delGoodsCommon($where);
            self::delGoodsById(['goods_id'=>$goods_id]);
        });
        return true;
    }
    static function getGoodsCount($condition,$count)
    {
        return Db::name('goods')->where($condition)->count($count);
    }
}