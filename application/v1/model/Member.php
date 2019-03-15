<?php
namespace app\v1\model;
use think\Model;
use think\DB;
/**
 * Class Member
 * @package app\v1\model 商家模型
 */
class Member extends Model{
    protected $pk = 'member_id';

    /** 查询商家信息
     * @param $condition
     * @param string $field
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getMemberInfo($condition, $field = '*')
    {
        return Db::name('member')->field($field)->where($condition)->find();
    }

    /**
     * @param $member_id
     * @param string $fields
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getMemberInfoByID($member_id, $fields = '*')
    {
        $member_info = self::getMemberInfo(array('member_id' => $member_id), $fields);
        return $member_info;
    }


}
