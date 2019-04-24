<?php

namespace App\model\V2;

use App\BModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Voucher extends BModel
{
    /**
     * @param $data
     * @return int
     */
    static function addVoucherTemplate($data)
    {
        return  BModel::insertData('voucher_template',$data);
    }

    /**
     * @param $condition
     * @return Model|\Illuminate\Database\Query\Builder|object|null
     */
    static function getVoucherQuotaInfo($condition)
    {
        return BModel::getTableFirstData('voucher_quota',$condition);
    }

    /**
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function getVoucherInfo($condition,$field=['*'])
    {
        return BModel::getTableFieldFirstData('voucher_template',$condition,$field);
    }

    /**
     * @param $condition
     * @param $data
     * @return int
     */
    static function upVoucherTemplate($condition,$data)
    {
        return BModel::upTableData('voucher_template',$condition,$data);
    }

    /**
     * @param $condition
     * @return int
     */
    static function getVoucherTemplateCount($condition)
    {
       return  BModel::getCount('voucher_template',$condition);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    static function  getVoucherPriceList()
    {
       return DB::table('voucher_price')
            ->orderBy('voucher_price','asc')
            ->get();
    }

    /**
     * @param $condition
     * @param array $field
     * @return \Illuminate\Support\Collection
     */
    static function  getVoucherTemplateList($condition,$field=['*'])
    {
        return BModel::getTableAllData('voucher_template',$condition,$field);
    }

    /**
     * @param $data
     * @return int
     */
    static function addBundlingData($data)
    {
        return  BModel::insertData('p_bundling',$data);
    }

    /**
     * @param $data
     * @return int
     */
    static function addBundlingGoodsData($data)
    {
        return  BModel::insertData('p_bundling_goods',$data);
    }

    /**
     * @param $condition
     * @param $data
     * @return int
     */
    static function upBundlingData($condition,$data)
    {
        return BModel::upTableData('p_bundling',$condition,$data);
    }

    /**
     * @param $condition
     * @param array $field
     * @return \Illuminate\Support\Collection
     */
    static function getBundlingData($condition,$field=['*'])
    {
        return BModel::getTableAllData('p_bundling',$condition,$field);
    }

    /**
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function  getBundling($condition,$field=['*'])
    {
        return BModel::getTableFieldFirstData('p_bundling',$condition,$field);
    }

    /**
     * @param $condition
     * @return Model|\Illuminate\Database\Query\Builder|object|null
     */
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

    /**
     * @param $condition
     * @param array $field
     * @return \Illuminate\Support\Collection
     */
    static function getBundlingGoods($condition,$field=['*'])
    {
        return DB::table('p_bundling_goods as a')
            ->leftJoin('goods as b','a.goods_id','b.goods_id')
            ->where($condition)
            ->get($field);
    }

    /**
     * @param $condition
     * @return int
     */
    static function delVoucher($condition)
    {
        return BModel::delData('voucher_template',$condition);
    }

    /**
     * @param $store_id
     * @param $bundling_id
     * @return mixed
     */
    static function getBundlingInfo($store_id,$bundling_id)
    {

        $data=self::getBundling(['bl_id'=>$bundling_id],['bl_id','bl_name','bl_discount_price as bl_price','bl_state']);
        $data->goods_list=self::getBundlingGoods(['a.bl_id'=>$bundling_id],['a.goods_id','a.goods_name','a.goods_image as img_name','a.bl_goods_price as goods_price','b.goods_price as goods_origin_price']);
        $data->img_path=getenv("GOODS_IMAGE").$store_id;
        return $data;
    }

    static function delBundling($condition)
    {
        DB::transaction(function () use($condition){
            BModel::delData('p_bundling',$condition);
            BModel::delData('p_bundling_goods',$condition);
        });
        return true;
    }

    /**
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function getManSongInfo($condition,$field=['*'])
    {
       return BModel::getTableFieldFirstData('p_mansong_quota',$condition,$field);
    }

    /**
     * @param $data
     * @return int
     */
    static function addManSongData($data)
    {
        return  BModel::insertData('p_mansong',$data);
    }

    /**
     * @param $data
     * @return int
     */
    static function addManSongRuleData($data)
    {
        return  BModel::insertData('p_mansong_rule',$data);
    }

    /**
     * @param $condition
     * @param array $field
     * @return \Illuminate\Support\Collection
     */
    static function getManSongList($condition,$field=['*'])
    {
        return BModel::getTableAllData('p_mansong',$condition,$field);
    }

    /**
     * @param $condition
     * @param array $field
     * @return \Illuminate\Support\Collection
     */
    static function getManSongRuleList($condition,$field=['*'])
    {
        return BModel::getTableAllData('p_mansong_rule',$condition,$field);
    }

    /**
     * @param $condition
     * @return bool
     */
    static function delMansong($condition)
    {
        DB::transaction(function ()use ($condition) {
            BModel::delData('p_mansong',$condition);
            BModel::delData('p_mansong_rule',$condition);
        });
        return true;
    }

    /**
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function getXianShiInfo($condition,$field=['*'])
    {
        return BModel::getTableFieldFirstData('p_xianshi_quota',$condition,$field);
    }

    /**
     * @param $data
     * @return int
     */
    static function addXianShiData($data)
    {
        return BModel::insertData('p_xianshi',$data);
    }

    /**
     * @param $condition
     * @param $data
     * @return int
     */
    static function upXianShiData($condition,$data)
    {
        return BModel::upTableData('p_xianshi',$condition,$data);
    }

    /**
     * @param $condition
     * @param $data
     * @return int
     */
    static function upManSongData($condition,$data)
    {
        return BModel::upTableData('p_mansong',$condition,$data);
    }

    /**
     * @param $data
     * @return int
     */
    static function addXianShiGoodsData($data)
    {
        return BModel::insertData('p_xianshi_goods',$data);
    }

    /**
     * @param $condition
     * @param array $field
     * @return \Illuminate\Support\Collection
     */
    static function getXianshiList($condition,$field=['*'])
    {
        return BModel::getTableAllData('p_xianshi',$condition,$field);
    }

    /**
     * @param $condition
     * @return bool
     */
    static function delXianshi($condition)
    {
        DB::transaction(function () use($condition) {
            BModel::delData('p_xianshi',$condition);
            BModel::delData('p_xianshi_goods',$condition);
        });
        return true;
    }

    /**
     * @param $field
     * @return \Illuminate\Support\Collection
     */
    static function getMianzhiList($field)
    {
        return DB::table('voucher_price')
            ->get($field);
    }

    /**
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function getXianshiData($condition,$field=['*'])
    {
        return BModel::getTableFieldFirstData('p_xianshi',$condition,$field);
    }

    /**
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function getXianshiGoodsData($condition,$field=['*'])
    {
        return BModel::getTableAllData('p_xianshi_goods',$condition,$field);
    }

    /**
     * @param $store_id
     * @param $xianshi_id
     * @return mixed
     */
    static function getXianshiInfoData($store_id,$xianshi_id)
    {
        $data=self::getXianshiData(['xianshi_id'=>$xianshi_id],['xianshi_id','xianshi_name','xianshi_title','xianshi_explain','start_time','end_time','lower_limit']);
        $data->goods_list=self::getXianshiGoodsData(['xianshi_id'=>$xianshi_id],['goods_id','goods_name','goods_image as img_name','xianshi_price','goods_price']);
        foreach ($data->goods_list as &$v)
        {
            $v->img_path=getenv("GOODS_IMAGE")..$store_id;
        }
        return $data;
    }


}
