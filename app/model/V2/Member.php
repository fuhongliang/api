<?php

namespace App\model\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Member extends Model
{
    /**
     * @param $condition
     * @return mixed
     */
    static function getMemberInfo($condition)
    {
        return DB::table('member')->where($condition)->first();
    }

    static function editMemberInfo($condition, $up_data)
    {
        return DB::table('member')
            ->where($condition)
            ->update($up_data);
    }
    static function checkStorePhoneExist($condition)
    {
        $count=DB::table('store')
            ->where($condition)
            ->count();
        return $count>0?false:true;
    }
    static function checkStoreJoinPhoneExist($condition)
    {
        $count=DB::table('store_joinin')
            ->where($condition)
            ->count();
        return $count>0?false:true;
    }
    static function checkStoreRegTmpExist($condition)
    {
        $count=DB::table('store_register_tmp')
            ->where($condition)
            ->count();
        return $count>0?false:true;
    }
    static function MemberRegister($data)
    {
        $member_id= DB::table('member')->insertGetId($data);
        DB::table('member_common')->insert(['member_id'=>$member_id,'auth_modify_pwd_time'=>time()]);
        return $member_id;
    }
    static function insertMemberRegTmpData($data)
    {
        return  DB::table('store_register_tmp')->insertGetId($data);
    }
}
