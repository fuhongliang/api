<?php

namespace App\model\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Voucher extends Model
{
    //
    static function addVoucherTemplate($data)
    {
        return  DB::table('voucher_template')->insertGetId($data);
    }
    static function getVoucherQuotaInfo($condition)
    {
        return DB::table('voucher_quota')
            ->where($condition)
            ->first();
    }
    static function addVoucherInfo($condition,$field=['*'])
    {
        return DB::table('voucher_template')
            ->where($condition)
            ->first($field);
    }
    static function upVoucherTemplate($condition,$data)
    {
        return DB::table('voucher_template')
            ->where($condition)
            ->update($data);
    }
    static function getVoucherTemplateCount($condition)
    {
       return  DB::table('voucher_template')
           ->where($condition)
           ->count();
    }
    static function  getVoucherPriceList()
    {
       return DB::table('voucher_price')
            ->orderBy('voucher_price','asc')
            ->get();
    }

    static function  getVoucherTemplateList($condition,$field=['*'])
    {
        return DB::table('voucher_template')
            ->where($condition)
            ->get($field);
    }

    static function addBundlingData($data)
    {
        return  DB::table('p_bundling')->insertGetId($data);
    }
    static function addBundlingGoodsData($data)
    {
        return  DB::table('p_bundling_goods')->insertGetId($data);
    }
    static function upBundlingData($condition,$data)
    {
        return DB::table('p_bundling')
            ->where($condition)
            ->update($data);
    }
    static function getBundlingData($condition,$field=['*'])
    {
        return DB::table('p_bundling')
            ->where($condition)
            ->get($field);
    }
    static function  getBundling($condition,$field=['*'])
    {
        return DB::table('p_bundling')
            ->where($condition)
            ->first($field);
    }
    static function getBundlingGoodsTotalPrice($condition)
    {
        return DB::table('p_bundling_goods')
        ->where($condition)
        ->first(
            array(
                DB::raw('IFNULL(SUM(bl_goods_price),0) as price')
            )
        );
    }
    static function getBundlingGoods($condition,$field=['*'])
    {
        return DB::table('p_bundling_goods')
            ->where($condition)
            ->get($field);
    }
    static function delVoucher($condition)
    {
        return DB::table('voucher_template')
            ->where($condition)
            ->delete();
    }
    static function getBundlingInfo($bundling_id)
    {
        $data=self::getBundling(['bl_id'=>$bundling_id],['bl_id','bl_name','bl_discount_price as bl_price','bl_state']);
        $data->goods_list=self::getBundlingGoods(['bl_id'=>$bundling_id],['goods_id','goods_name','goods_image','bl_goods_price as goods_price']);
        return $data;
    }

    static function delBundling($condition)
    {
        DB::transaction(function () use($condition){
            DB::table('p_bundling')
                ->where($condition)
                ->delete();
            DB::table('p_bundling_goods')
                ->where($condition)
                ->delete();
        });
        return true;
    }
    static function getManSongInfo($condition,$field=['*'])
    {
       return DB::table('p_mansong_quota')
            ->where($condition)
            ->get($field)
            ->first();
    }
    static function addManSongData($data)
    {
        return  DB::table('p_mansong')->insertGetId($data);
    }
    static function addManSongRuleData($data)
    {
        return  DB::table('p_mansong_rule')->insertGetId($data);
    }
    static function getManSongList($condition,$field=['*'])
    {
        return DB::table('p_mansong')
            ->where($condition)
            ->get($field);
    }
    static function getManSongRuleList($condition,$field=['*'])
    {
        return DB::table('p_mansong_rule')
            ->where($condition)
            ->get($field);
    }
    static function delMansong($condition)
    {
        DB::transaction(function ()use ($condition) {
            DB::table('p_mansong')
                ->where($condition)
                ->delete();
            DB::table('p_mansong_rule')
                ->where($condition)
                ->delete();
        });
        return true;
    }
    static function getXianShiInfo($condition,$field=['*'])
    {
        return DB::table('p_xianshi_quota')
            ->where($condition)
            ->get($field)
            ->first();
    }
    static function addXianShiData($data)
    {
        return  DB::table('p_xianshi')->insertGetId($data);;
    }
    static function upXianShiData($condition,$data)
    {
        return DB::table('p_xianshi')
            ->where($condition)
            ->update($data);
    }
    static function addXianShiGoodsData($data)
    {
        return  DB::table('p_xianshi_goods')->insertGetId($data);;
    }
    static function getXianshiList($condition,$field=['*'])
    {
        return DB::table('p_xianshi')
            ->where($condition)
            ->get($field);
    }
    static function delXianshi($condition)
    {
        DB::transaction(function () use($condition) {
            DB::table('p_xianshi')
                ->where($condition)
                ->delete();
            DB::table('p_xianshi_goods')
                ->where($condition)
                ->delete();
        });
        return true;
    }
    static function getMianzhiList($field)
    {
        return DB::table('voucher_price')
            ->get($field);
    }
    static function getXianshiData($condition,$field=['*'])
    {
        return  DB::table('p_xianshi')
        ->where($condition)
        ->first($field);
    }
    static function getXianshiGoodsData($condition,$field=['*'])
    {
        return DB::table('p_xianshi_goods')
            ->where($condition)
            ->get($field);
    }
    static function getXianshiInfoData($store_id,$xianshi_id)
    {
        $data=self::getXianshiData(['xianshi_id'=>$xianshi_id],['xianshi_id','xianshi_name','xianshi_title','xianshi_explain','start_time','end_time','lower_limit']);

        $data->goods_list=self::getXianshiGoodsData(['xianshi_id'=>$xianshi_id],['goods_id','goods_name','goods_image as img_name','xianshi_price','goods_price']);
        $data->img_path=getenv('GOODS_IMAGE').$store_id;
        return $data;
    }


}
