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
    static function getBundlingData($condition,$field=['*'])
    {
        return DB::table('p_bundling')
            ->where($condition)
            ->get($field);
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
    static function delVoucher($condition)
    {
        return DB::table('voucher_template')
            ->where($condition)
            ->delete();
    }
    static function delBundling($condition)
    {
        DB::transaction(function () {
            DB::table('voucher_template')
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
        DB::transaction(function () {
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
        DB::transaction(function () {
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

}
