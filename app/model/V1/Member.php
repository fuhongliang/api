<?php

namespace App\model\V1;

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
}
