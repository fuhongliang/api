<?php

namespace App\model\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Goods extends Model
{
    static function getGoodsInfo($condition, $field = ['*'])
    {
        return DB::table('goods as a')
            ->where($condition)
            ->leftJoin('goods_common as b','a.goods_commonid','b.goods_commonid')
            ->get($field)
            ->first();
    }
    static function getGoodsCommonInfo($condition, $field = ['*'])
    {
        return DB::table('goods_common')->where($condition)->get($field)->first();
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
        DB::transaction(function () use ($goods_id,$store_id){
            $data= self::getGoodsInfo(['goods_id'=>$goods_id],['a.goods_commonid']);
            $where=array();
            $where['goods_lock']=0;
            $where['goods_commonid']=$data->goods_commonid;
            $file_name=self::getGoodsField(['goods_id'=>$goods_id],'goods_image');
            self::delGoodsCommon($where);
            self::delGoodsById(['goods_id'=>$goods_id]);
            if($file_name)
            {
                $img_path = '/shop/store/goods' . '/' . $store_id  .'/'. $file_name;
                Storage::disk('public')->delete($img_path);
            }
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
        return $goods_state;
    }
    static function getGoodsField($condition,$value)
    {
        return DB::table('goods')->where($condition)->value($value);
    }
    static function getGoodsCommonField($condition,$value)
    {
        return DB::table('goods_common')->where($condition)->value($value);
    }
    static function upGoodsField($condition,$update)
    {
        return DB::table('goods')->where($condition)->update($update);
    }
    static function upGoodsCommonField($condition,$update)
    {
        return DB::table('goods_common')->where($condition)->update($update);
    }
}
