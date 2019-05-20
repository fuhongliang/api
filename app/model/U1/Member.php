<?php

namespace App\model\U1;

use App\BModel;
use App\model\V3\Goods;
use Illuminate\Support\Facades\DB;

class Member extends BModel
{
    /**获取顶级商品分类
     * @return \Illuminate\Support\Collection
     */
    static function getParentGoodsClass()
    {
        $data = BModel::getTableAllOrderData('goods_class', ['gc_parent_id' => 0, 'gc_show' => 1], 'gc_sort', ['gc_title', 'gc_id', 'icon_image']);
        return $data->isEmpty() ? array() : $data->toArray();
    }

    /**优惠专区
     * @return array
     */
    static function getAppDiscount()
    {
        $data = BModel::getTableAllOrderData('app_discount', ['is_show' => 1], 'sort', ['type', 'background_image', 'title', 'brief']);
        return $data->isEmpty() ? array() : $data->toArray();
    }

    static function getStoreList($keyword, $page)
    {
        $result    = [];
        $skip      = ($page - 1) * 10;
        $store_ids = DB::table('goods as a')
            ->leftJoin('store as b', 'a.store_id', 'b.store_id')
            ->where('a.goods_name', 'like', '%' . $keyword . '%')
            ->orWhere('b.store_name', 'like', '%' . $keyword . '%')
            ->orderBy('b.store_id', 'desc')
            ->skip($skip)
            ->take(10)
            ->distinct()
            ->get(['b.store_id']);
        if (!$store_ids->isEmpty()) {
            $storeIds = $store_ids->toArray();
            foreach ($storeIds as $k => $val) {
                $store_data          = BModel::getTableFieldFirstData('store', ['store_id' => $val->store_id], ['store_id', 'store_name', 'store_avatar', 'store_sales', 'store_credit']);
                $result[$k]          = $store_data;
                $xianshi_data        = BModel::getTableAllData('p_xianshi', ['store_id' => $val->store_id, 'state' => 1], ['xianshi_name', 'xianshi_id']);
                $result[$k]->xianshi = $xianshi_data->isEmpty() ? array() : $xianshi_data->toArray();
                $manjian             = BModel::getLeftData('p_mansong_rule AS a', 'p_mansong AS b', 'a.mansong_id', 'b.mansong_id', ['b.store_id' => $val->store_id], ['a.price', 'a.discount']);
                $result[$k]->manjian = $manjian->isEmpty() ? [] : $manjian->toArray();
            }
        }
        return $result;
    }

    static function getStoreVoucher($store_id)
    {
        return DB::table('voucher')
            ->where('voucher_store_id', $store_id)
            ->where('voucher_start_date', '<', time())
            ->where('voucher_end_date', '>', time())
            ->where('voucher_state', 1)
            ->sum('voucher_price');

    }

    static function getStoreGoodsListByStcId($store_id, $class_id)
    {
        $goods_info = $ids = array();
        $fields     = ['a.goods_id', 'a.goods_name', 'a.goods_price', 'a.goods_marketprice', 'b.goods_body as goods_desc', 'a.goods_image as img_name', 'a.goods_salenum'];
        if (!$class_id || $class_id == 'hot') {
            $data = DB::table('goods as a')
                ->leftJoin('goods_common as b', 'a.goods_commonid', 'b.goods_commonid')
                ->where('a.store_id', $store_id)
                ->where('a.goods_state', 1)
                ->orderBy('a.goods_salenum', 'desc')
                ->get($fields);
            return $data->isEmpty() ? $goods_info : $data->toArray();
        } elseif ($class_id == 'zhekou') {
            $data = DB::table('p_xianshi')
                ->where('store_id', $store_id)
                ->where('state', 1)
                ->orderBy('xianshi_id', 'desc')
                ->get(['xianshi_id', 'xianshi_name as goods_name', 'xianshi_explain as goods_desc']);
            if (!$data->isEmpty()) {
                $xianshi_data = $data->toArray();
                foreach ($xianshi_data as $k => $val) {
                    $goods_info[$k]['goods_id']          = $val->xianshi_id;
                    $goods_info[$k]['goods_name']        = $val->goods_name;
                    $goods_info[$k]['goods_desc']        = $val->goods_desc;
                    $goods_info[$k]['goods_price']       = BModel::getSum('p_xianshi_goods', ['xianshi_id' => $val->xianshi_id], 'goods_price');
                    $goods_info[$k]['img_name']          = BModel::getTableValue('p_xianshi_goods', ['xianshi_id' => $val->xianshi_id], 'goods_image');
                    $goods_info[$k]['goods_salenum']     = 999;
                    $goods_info[$k]['goods_marketprice'] = BModel::getSum('p_xianshi_goods', ['xianshi_id' => $val->xianshi_id], 'xianshi_price');
                }
            }
            return $goods_info;
        } elseif ($class_id == 'youhui') {
            $data = DB::table('p_bundling')
                ->where('store_id', $store_id)
                ->where('bl_state', 1)
                ->orderBy('bl_id', 'desc')
                ->get(['bl_id', 'bl_name as goods_name', 'bl_name as goods_desc']);
            if (!$data->isEmpty()) {
                $youhui_data = $data->toArray();
                foreach ($youhui_data as $k => $val) {
                    $goods_info[$k]['goods_id']      = $val->bl_id;
                    $goods_info[$k]['goods_name']    = $val->goods_name;
                    $goods_info[$k]['goods_desc']    = $val->goods_desc;
                    $goods_info[$k]['goods_price']   = BModel::getSum('p_bundling_goods', ['bl_id' => $val->bl_id], 'bl_goods_price');
                    $goods_info[$k]['img_name']      = BModel::getTableValue('p_bundling_goods', ['bl_id' => $val->bl_id], 'goods_image');
                    $goods_info[$k]['goods_salenum'] = 999;
                    $goods_ids                       = BModel::getTableAllData('p_bundling_goods', ['bl_id' => $val->bl_id], ['goods_id']);
                    $gids                            = [];
                    foreach ($goods_ids as $goods_id) {
                        array_push($gids, $goods_id->goods_id);
                    }
                    $goods_marketprice                   = DB::table('goods')->whereIn('goods_id', $gids)->sum('goods_marketprice');
                    $goods_info[$k]['goods_marketprice'] = $goods_marketprice;
                }
            }
            return $goods_info;
        }
        $data = DB::table('goods')
            ->where('store_id', $store_id)
            ->whereNotNull('goods_stcids')
            ->get(['goods_id', 'goods_stcid']);
        if (empty($data)) {
            return $goods_info;
        } else {
            foreach ($data as $val) {
                if (!empty($val->goods_stcid)) {
                    if ($class_id == $val->goods_stcid) {
                        array_push($ids, $val->goods_id);
                    }
                }
            }
            if (!empty($ids)) {
                foreach ($ids as $k => $goods_id) {

                    $goods_info[$k] = Goods::getGoodsInfo(['goods_id' => $goods_id], $fields);
                }
            }
            return $goods_info;
        }
    }

    static function getHotGoods($condition, $order, $limit, $fields)
    {
        return DB::table('goods')->where($condition)->orderBy($order, 'desc')->limit($limit)->get($fields);
    }


    /**
     * 查询出售中的商品列表及其促销信息
     * @param array $goodsid_array
     * @return array
     */
    static function getGoodsOnlineListAndPromotionByIdArray($goodsid_array) {
        if (empty($goodsid_array) || !is_array($goodsid_array)) return array();

        $goods_list = array();
        foreach ($goodsid_array as $goods_id) {
            $goods_info = self::getGoodsOnlineInfoAndPromotionById($goods_id);
            if (!empty($goods_info)) $goods_list[] = $goods_info;
        }
        return $goods_list;
    }
    /**
     * 查询出售中的商品详细信息及其促销信息
     * @param int $goods_id
     * @return array
     */
    static function getGoodsOnlineInfoAndPromotionById($goods_id) {
        $goods_info = self::getGoodsInfoAndPromotionById($goods_id);
        if (empty($goods_info) || $goods_info->goods_state != 1|| $goods_info->goods_verify != 1) {
            return array();
        }
        return $goods_info;
    }
    /**
     * 查询商品详细信息及其促销信息
     * @param int $goods_id
     * @return array
     */
    static function getGoodsInfoAndPromotionById($goods_id) {
        $goods_info = BModel::getTableFirstData('goods',['goods_id'=>$goods_id]);
        if (empty($goods_info)) {
            return array();
        }
        return $goods_info;
    }



}
