<?php

namespace App\model\V3;

use App\BModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Goods extends BModel
{
    /** 获取商品信息
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function getGoodsInfo($condition, $field = ['*'])
    {
        return DB::table('goods as a')
            ->where($condition)
            ->leftJoin('goods_common as b','a.goods_commonid','b.goods_commonid')
            ->get($field)
            ->first();
    }

    /** 商品附属信息
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function getGoodsCommonInfo($condition, $field = ['*'])
    {
        return BModel::getTableFieldFirstData('goods_common',$condition,$field);
    }

    /** 添加商品附属信息
     * @param $data
     * @return int
     */
    static function addGoodsCommon($data)
    {
        return BModel::insertData('goods_common',$data);
    }

    /**添加商品信息
     * @param $data
     * @return int
     */
    static function addGoods($data)
    {
        return BModel::insertData('goods',$data);
    }

    /** 删除商品
     * @param $store_id
     * @param $goods_id
     * @return bool
     */
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

    /** 删除商品附属信息
     * @param $condition
     * @return int
     */
    static function delGoodsCommon($condition)
    {
        return BModel::delData('goods_common',$condition);
    }

    /** 删除商品
     * @param $condition
     * @return int
     */
    static function delGoodsById($condition)
    {
        return BModel::delData('goods',$condition);
    }

    /**统计商品信息
     * @param $condition
     * @return int
     */
    static function getGoodsCount($condition)
    {
        return BModel::getCount('goods',$condition);
    }

    /** 商品上下架
     * @param $goods_id
     * @param $store_id
     * @return int|mixed
     */
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
           BModel::upTableData('goods',['goods_id'=>$goods_id,'store_id'=>$store_id],['goods_state' => $goods_state]);
        });
        return $goods_state;
    }

    /**获取商品某些字段
     * @param $condition
     * @param $value
     * @return mixed
     */
    static function getGoodsField($condition,$value)
    {
        return BModel::getTableValue('goods',$condition,$value);
    }

    /**获取商品附属信息某些字段
     * @param $condition
     * @param $value
     * @return mixed
     */
    static function getGoodsCommonField($condition,$value)
    {
        return BModel::getTableValue('goods_common',$condition,$value);
    }

    /**更新信息
     * @param $condition
     * @param $update
     * @return int
     */
    static function upGoodsField($condition,$update)
    {
        return BModel::upTableData('goods',$condition,$update);
    }

    /**更新商品附属信息
     * @param $condition
     * @param $update
     * @return int
     */
    static function upGoodsCommonField($condition,$update)
    {
        return BModel::upTableData('goods_common',$condition,$update);
    }
}
