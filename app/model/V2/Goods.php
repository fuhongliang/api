<?php

namespace App\model\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Goods extends Model
{
    static function getGoodsInfo($condition, $field = ['*'])
    {
        return DB::table('goods')->where($condition)->get($field)->first();
    }
    static function addGoodsCommon($data)
    {
        return  DB::table('goods_common')->insertGetId($data);
    }
    static function addGoods($data)
    {
        return  DB::table('goods')->insertGetId($data);
    }
    static function delGoods($store_id,$goods_id)
    {
        DB::transaction(function () use ($goods_id){
            $data= self::getGoodsInfo(['goods_id'=>$goods_id],['goods_commonid']);
            $where=array();
            $where['goods_lock']=0;
            $where['goods_commonid']=$data->goods_commonid;
            self::delGoodsCommon($where);
            self::delGoodsById(['goods_id'=>$goods_id]);
        });
        return true;
    }
    static function delGoodsCommon($condition)
    {
        return  DB::table('goods_common')->where($condition)->delete();
    }
    static function delGoodsById($condition)
    {
        return  DB::table('goods')->where($condition)->delete();
    }
    static function getGoodsCount($condition)
    {
        return DB::table('goods')->where($condition)->count();
    }
    static function changeGoodsState($goods_id,$store_id)
    {
        $goods_state=DB::table('goods')->where(['goods_id'=>$goods_id,'store_id'=>$store_id])->value('goods_state');
        if ($goods_state ==1)
        {
            $goods_state=0;
        }elseif ($goods_state==0)
        {
            $goods_state=1;
        }
        DB::transaction(function () use ($goods_id,$store_id,$goods_state){
            DB::table('goods')->where(['goods_id'=>$goods_id,'store_id'=>$store_id])->update(['goods_state' => $goods_state]);
        });
        return true;
    }
}
