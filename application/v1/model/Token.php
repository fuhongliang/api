<?php
namespace app\v1\model;
use think\Model;
use think\DB;

/**
 * Class Token
 * @package app\v1\model
 */
class Token extends Model{
    /**
     * @param $data
     * @return int|string
     */
    static function addToken($data)
    {
        return  Db::name('token')->insertGetId($data);
    }

    /**
     * @param $condition
     * @param $field
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getTokenField($condition,$field)
    {
        return Db::name('token')->field($field)->where($condition)->find();

    }


}