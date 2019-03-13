<?php
namespace app\v1\model;
use think\Model;
use think\DB;
/**
 * Class Member
 * @package app\v1\model 商家模型
 */
class Seller extends Model{
    protected $pk = 'seller_id';

    /**
     * @param $condition  获取用户信息
     * @param string $field
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getSellerInfo($condition, $field = '*')
    {
        return Db::name('seller')->field($field)->where($condition)->find();
    }



}
