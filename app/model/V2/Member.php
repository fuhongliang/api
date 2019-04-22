<?php

namespace App\model\V2;

use App\BModel;
use Illuminate\Support\Facades\DB;

class Member extends BModel
{
    /** 获取一条会员数据
     * @param $condition
     * @return mixed
     */
    static function getMemberInfo($condition)
    {
        return BModel::getTableFirstData('member',$condition);
    }

    /** 修改会员信息
     * @param $condition
     * @param $up_data
     * @return int
     */
    static function editMemberInfo($condition, $up_data)
    {
        return BModel::upTableData('member',$condition,$up_data);
    }

    /** 检查手机号是否存在
     * @param $condition
     * @return bool
     */
    static function checkStorePhoneExist($condition)
    {
        $count=BModel::getCount('store',$condition);
        return $count>0 ? false : true;
    }

    /**检查手机号是否存在
     * @param $condition
     * @return bool
     */
    static function checkStoreJoinPhoneExist($condition)
    {
        $count=BModel::getCount('store_joinin',$condition);
        return $count>0?false:true;
    }

    /**检查手机号是否存在
     * @param $condition
     * @return bool
     */
    static function checkStoreRegTmpExist($condition)
    {
        $count=BModel::getCount('store_register_tmp',$condition);
        return $count>0?false:true;
    }

    /**注册会员信息
     * @param $data
     * @return int
     */
    static function MemberRegister($data)
    {
        $member_id= BModel::insertData('member',$data);
        BModel::insertData('member_common',['member_id'=>$member_id,'auth_modify_pwd_time'=>time()]);
        return $member_id;
    }

    /**添加临时商家注册记录
     * @param $data
     * @return int
     */
    static function insertMemberRegTmpData($data)
    {
       return  BModel::insertData('store_register_tmp',$data);
    }
}
