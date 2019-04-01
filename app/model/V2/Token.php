<?php

namespace App\model\V2;

use App\Http\Controllers\BaseController as Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Token extends Model
{ /**
 * @param $data
 * @return int|string
 */
    static function addToken($data)
    {
        return  DB::table('token')->insertGetId($data);
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
        return DB::table('token')
            ->where($condition)
            ->get($field)
            ->first();
    }



}
