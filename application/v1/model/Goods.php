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

}