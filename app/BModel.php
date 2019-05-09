<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BModel extends Model
{
    /** 根据表名获取第一条数据
     * @param $table
     * @param $condition
     * @return Model|\Illuminate\Database\Query\Builder|object|null
     */
    static function getTableFirstData($table,$condition)
    {
        return DB::table($table)->where($condition)->first();
    }

    /**获取所有信息
     * @param $table
     * @param $condition
     * @param array $fields
     * @return \Illuminate\Support\Collection
     */
    static function getTableAllData($table,$condition,$fields=['*'])
    {
        return DB::table($table)->where($condition)->get($fields);
    }

    /**
     * @param $table
     * @param $condition
     * @param $order
     * @param array $fields
     * @return \Illuminate\Support\Collection
     */
    static function getTableAllOrderData($table,$condition,$order,$fields=['*'])
    {
        return DB::table($table)->where($condition)->orderBy($order,'desc')->get($fields);
    }
    /** 获取第一条数据中部分信息
     * @param $table
     * @param $condition
     * @param $field
     * @return mixed
     */
    static function getTableFieldFirstData($table,$condition,$field)
    {
        return DB::table($table)->where($condition)->get($field)->first();
    }

    /**获取单列信息
     * @param $table
     * @param $condition
     * @param $value
     * @return mixed
     */
    static function getTableValue($table,$condition,$value)
    {
        return DB::table($table)->where($condition)->value($value);
    }
    /**修改信息
     * @param $table
     * @param $condition
     * @param $data
     * @return int
     */
    static function upTableData($table,$condition,$data)
    {
        return DB::table($table)
            ->where($condition)
            ->update($data);
    }

    /** 获取数据数量
     * @param $table
     * @param $condition
     * @return int
     */
    static function getCount($table,$condition)
    {
        return DB::table($table)
            ->where($condition)
            ->count();
    }

    /** 添加数据
     * @param $table
     * @param $data
     * @return int
     */
    static function insertData($table,$data)
    {
        return DB::table($table)->insertGetId($data);
    }

    /** 删除信息
     * @param $table
     * @param $condition
     * @return int
     */
    static function delData($table,$condition)
    {
        return  DB::table($table)->where($condition)->delete();
    }

    /**求和
     * @param $table
     * @param $condition
     * @param $field
     * @return mixed
     */
    static function getSum($table,$condition,$field)
    {
        return   DB::table($table)->where($condition)->sum($field);
    }

}
