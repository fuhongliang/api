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




}
