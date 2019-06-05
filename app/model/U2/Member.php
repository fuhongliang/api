<?php

namespace App\model\U2;

use App\BModel;
use App\Http\Controllers\BaseController;
use App\model\V3\Goods;
use Illuminate\Support\Facades\DB;

class Member extends BModel
{
    /**获取顶级商品分类
     * @return \Illuminate\Support\Collection
     */
    static function getParentGoodsClass()
    {
        $data = BModel::getTableAllOrderData('goods_class', ['gc_parent_id' => 0, 'gc_show' => 1], 'gc_sort', ['gc_name', 'gc_id', 'icon_image']);
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

    static function getStoreList($keyword, $page, $type)
    {
        $result = [];
        $skip = ($page - 1) * 10;
        if (!$type || $type == 1)//默认
        {
            $store_ids = DB::table('goods as a')
                ->leftJoin('store as b', 'a.store_id', 'b.store_id')
                ->where('a.goods_name', 'like', '%' . $keyword . '%')
                ->orWhere('b.store_name', 'like', '%' . $keyword . '%')
                ->orderBy('b.store_id', 'desc')
                ->skip($skip)
                ->take(10)
                ->distinct()
                ->get(['b.store_id']);
        } elseif ($type == 2)//销量
        {
            $store_ids = DB::table('goods as a')
                ->leftJoin('store as b', 'a.store_id', 'b.store_id')
                ->where('a.goods_name', 'like', '%' . $keyword . '%')
                ->orWhere('b.store_name', 'like', '%' . $keyword . '%')
                ->orderBy('b.store_sales', 'desc')
                ->skip($skip)
                ->take(10)
                ->distinct()
                ->get(['b.store_id']);
        } elseif ($type == 3) {
            $store_ids = DB::table('goods as a')
                ->leftJoin('store as b', 'a.store_id', 'b.store_id')
                ->where('a.goods_name', 'like', '%' . $keyword . '%')
                ->orWhere('b.store_name', 'like', '%' . $keyword . '%')
                ->orderBy('b.store_credit', 'desc')
                ->skip($skip)
                ->take(10)
                ->distinct()
                ->get(['b.store_id']);
        } elseif ($type == 4) {
            $store_ids = DB::table('goods as a')
                ->leftJoin('store as b', 'a.store_id', 'b.store_id')
                ->where('a.goods_name', 'like', '%' . $keyword . '%')
                ->orWhere('b.store_name', 'like', '%' . $keyword . '%')
                ->orderBy('b.store_credit', 'desc')
                ->skip($skip)
                ->take(10)
                ->distinct()
                ->get(['b.store_id']);
        }
        if (!$store_ids->isEmpty()) {
            $storeIds = $store_ids->toArray();
            foreach ($storeIds as $k => $val) {
                $store_data = BModel::getTableFieldFirstData('store', ['store_id' => $val->store_id], ['store_id', 'store_name', 'store_avatar', 'store_sales', 'store_credit']);
                $result[$k] = $store_data;
                $xianshi_data = BModel::getTableAllData('p_xianshi', ['store_id' => $val->store_id, 'state' => 1], ['xianshi_name', 'xianshi_id']);
                $result[$k]->xianshi = $xianshi_data->isEmpty() ? array() : $xianshi_data->toArray();
                $manjian = BModel::getLeftData('p_mansong_rule AS a', 'p_mansong AS b', 'a.mansong_id', 'b.mansong_id', ['b.store_id' => $val->store_id], ['a.rule_id', 'a.price', 'a.discount']);
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
        $fields = ['a.goods_id', 'a.goods_name', 'a.goods_price', 'a.goods_marketprice', 'b.goods_body as goods_desc', 'a.goods_image as img_name', 'a.goods_salenum'];
        if (!$class_id || $class_id == 'hot') {
            $data = DB::table('goods as a')
                ->leftJoin('goods_common as b', 'a.goods_commonid', 'b.goods_commonid')
                ->where('a.store_id', $store_id)
                ->where('a.goods_state', 1)
                ->orderBy('a.goods_salenum', 'desc')
                ->get($fields);
            if (!$data->isEmpty()) {
                foreach ($data as &$datum) {
                    $datum->zan = BModel::getCount('goods_zan', ['goods_id' => $datum->goods_id]);
                }
            }
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
                    $goods_info[$k]['goods_id'] = $val->xianshi_id;
                    $goods_info[$k]['goods_name'] = $val->goods_name;
                    $goods_info[$k]['goods_desc'] = $val->goods_desc;
                    $goods_info[$k]['goods_price'] = BModel::getSum('p_xianshi_goods', ['xianshi_id' => $val->xianshi_id], 'goods_price');
                    $goods_info[$k]['img_name'] = BModel::getTableValue('p_xianshi_goods', ['xianshi_id' => $val->xianshi_id], 'goods_image');
                    $goods_info[$k]['goods_salenum'] = 999;
                    $goods_info[$k]['zan'] = BModel::getCount('goods_zan', ['goods_id' => $datum->goods_id]);
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
                    $goods_info[$k]['goods_id'] = $val->bl_id;
                    $goods_info[$k]['goods_name'] = $val->goods_name;
                    $goods_info[$k]['goods_desc'] = $val->goods_desc;
                    $goods_info[$k]['goods_price'] = BModel::getSum('p_bundling_goods', ['bl_id' => $val->bl_id], 'bl_goods_price');
                    $goods_info[$k]['img_name'] = BModel::getTableValue('p_bundling_goods', ['bl_id' => $val->bl_id], 'goods_image');
                    $goods_info[$k]['goods_salenum'] = 999;
                    $goods_info[$k]['zan'] = BModel::getCount('goods_zan', ['goods_id' => $datum->goods_id]);
                    $goods_ids = BModel::getTableAllData('p_bundling_goods', ['bl_id' => $val->bl_id], ['goods_id']);
                    $gids = [];
                    foreach ($goods_ids as $goods_id) {
                        array_push($gids, $goods_id->goods_id);
                    }
                    $goods_marketprice = DB::table('goods')->whereIn('goods_id', $gids)->sum('goods_marketprice');
                    $goods_info[$k]['goods_marketprice'] = $goods_marketprice;
                }
            }
            return $goods_info;
        } else {
            $data = DB::table('goods')
                ->where('store_id', $store_id)
                ->whereNotNull('goods_stcids')
                ->get(['goods_id', 'goods_stcid']);
            if (empty($data)) {
                return $goods_info;
            } else {
                foreach ($data as $val) {
                    if (!empty($val->goods_stcid)) {
                        if (intval($class_id) == $val->goods_stcid) {
                            array_push($ids, $val->goods_id);
                        }
                    }
                }
                if (!empty($ids)) {
                    foreach ($ids as $k => $goods_id) {
                        $goods_info[$k] = Goods::getGoodsInfo(['goods_id' => $goods_id], $fields);
                        $goods_info[$k]->zan = BModel::getCount('goods_zan', ['goods_id' => $goods_id]);
                    }
                }
                return $goods_info;
            }
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
    static function getGoodsOnlineListAndPromotionByIdArray($goodsid_array)
    {
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
    static function getGoodsOnlineInfoAndPromotionById($goods_id)
    {
        $goods_info = self::getGoodsInfoAndPromotionById($goods_id);
        if (empty($goods_info) || $goods_info->goods_state != 1 || $goods_info->goods_verify != 1) {
            return array();
        }
        return $goods_info;
    }

    /**
     * 查询商品详细信息及其促销信息
     * @param int $goods_id
     * @return array
     */
    static function getGoodsInfoAndPromotionById($goods_id)
    {
        $goods_info = BModel::getTableFirstData('goods', ['goods_id' => $goods_id]);
        if (empty($goods_info)) {
            return array();
        }
        return $goods_info;
    }

    static function getGoodsComData($condition, $member_id, $goods_id, $field = ['*'])
    {
        $result = [];
        $data = DB::table('evaluate_goods AS a')
            ->leftJoin('member as b', 'a.geval_frommemberid', 'b.member_id')
            ->where($condition)
            ->orderBy('geval_addtime', 'desc')
            ->get($field);

        if (!$data->isEmpty()) {
            foreach ($data as $k => $v) {
                $result[$k]['member_name'] = is_null($v->member_name) ? "" : $v->member_name;
                $result[$k]['member_avatar'] = is_null($v->member_avatar) ? "" : $v->member_avatar;
                $result[$k]['geval_content'] = is_null($v->geval_content) ? "" : $v->geval_content;
                $result[$k]['geval_addtime'] = date('Y.m.d', $v->geval_addtime);
                $result[$k]['is_zan'] = BModel::getCount('goods_zan', ['member_id' => $member_id, 'goods_id' => $goods_id]);
            }
        }
        return $result;
    }

    static function getManyi($store_id)
    {
        return DB::table('store_com')->where('store_id', $store_id)->whereIn('haoping', [1, 2])->count();
    }

    static function getBuManyi($store_id)
    {
        return DB::table('store_com')->where('store_id', $store_id)->whereNotIn('haoping', [1, 2])->count();
    }

    static function getYoutu($store_id)
    {
        return DB::table('store_com')->where('store_id', $store_id)->whereNotNull('images')->count();
    }

    static function getStoreComList($store_id, $type)
    {
        $result = [];
        $field = ['a.content', 'a.haoping', 'a.images', 'a.add_time', 'a.is_replay', 'a.parent_id', 'b.member_name', 'b.member_avatar'];
        if (!$type || $type == 1) {
            $data = DB::table('store_com AS a')
                ->leftJoin('member as b', 'a.member_id', 'b.member_id')
                ->where('a.store_id', $store_id)
                ->orderBy('a.add_time', 'desc')
                ->get($field);
        } elseif ($type == 2) {
            $data = DB::table('store_com AS a')
                ->leftJoin('member as b', 'a.member_id', 'b.member_id')
                ->where('a.store_id', $store_id)
                ->whereIn('a.haoping', [1, 2])
                ->orderBy('a.add_time', 'desc')
                ->get($field);
        } elseif ($type == 3) {
            $data = DB::table('store_com AS a')
                ->leftJoin('member as b', 'a.member_id', 'b.member_id')
                ->where('a.store_id', $store_id)
                ->whereNotIn('a.haoping', [1, 2])
                ->orderBy('a.add_time', 'desc')
                ->get($field);
        } elseif ($type == 4) {
            $data = DB::table('store_com AS a')
                ->leftJoin('member as b', 'a.member_id', 'b.member_id')
                ->where('a.store_id', $store_id)
                ->whereNotNull('images')
                ->orderBy('a.add_time', 'desc')
                ->get($field);
        }
        if ($data->isEmpty()) {
            return array();
        }
        $datas = $data->toArray();
        foreach ($datas as $k => $v) {
            $result[$k]['content'] = is_null($v->content) ? "" : $v->content;
            $result[$k]['haoping'] = $v->haoping;
            $result[$k]['images'] = explode(',', $v->images);
            $result[$k]['add_time'] = date('Y-m-d', $v->add_time);
            $result[$k]['member_name'] = $v->member_name;
            $result[$k]['member_avator'] = is_null($v->member_avatar) ? "" : $v->member_avatar;
            if ($v->is_replay == 1) {
                $result[$k]['replay'] = BModel::getTableValue('store_com', ['com_id' => $v->parent_id],'content');
            } else {
                $result[$k]['replay'] = '';
            }
        }
        return $result;
    }

    static function getManSongCount($store_id, $total_amount)
    {
        $discount = DB::table('p_mansong_rule as a')
            ->leftJoin('p_mansong as b', 'a.mansong_id', 'b.mansong_id')
            ->where('a.price', '<=', $total_amount)
            ->where('b.store_id', $store_id)
            ->orderBy('a.price', 'desc')
            ->limit(1)
            ->value('a.discount');
        return !$discount ? 0 : $discount;
    }

    static function getVoucherCount($store_id, $member_id, $amount)
    {
        $voucher_price = DB::table('voucher')
            ->where('voucher_store_id', $store_id)
            ->where('voucher_owner_id', $member_id)
            ->where('voucher_limit', '<', $amount)
            ->where('voucher_end_date', '<', time())
            ->orderBy('voucher_price', 'desc')
            ->limit(1)
            ->value('voucher_price');
        return !$voucher_price ? 0 : $voucher_price;
    }

    static function getUserVoucherList($store_id, $member_id, $amount)
    {
        $voucher = DB::table('voucher')
            ->where('voucher_store_id', $store_id)
            ->where('voucher_owner_id', $member_id)
            ->where('voucher_limit', '<', $amount)
            ->where('voucher_end_date', '<', time())
            ->orderBy('voucher_price', 'desc')
            ->get(['voucher_price', 'voucher_id']);
        return $voucher->isEmpty() ? [] : $voucher->toArray();
    }

    static function getAllOrder($member_id)
    {
        $data = DB::table('order as a')
            ->leftJoin('order_common as b', 'a.order_id', 'b.order_id')
            ->leftJoin('store as c', 'a.store_id', 'c.store_id')
            ->where('a.buyer_id', $member_id)
            ->get(['a.order_id', 'c.store_name', 'c.store_avatar', 'a.order_state']);
        return $data->isEmpty() ? [] : $data->toArray();
    }

    static function getEvaluationOrder($member_id)
    {
        $data = DB::table('order as a')
            ->leftJoin('order_common as b', 'a.order_id', 'b.order_id')
            ->leftJoin('store as c', 'a.store_id', 'c.store_id')
            ->where('a.buyer_id', $member_id)
            ->where('a.order_state',40)
            ->where('evaluation_state', 0)
            ->get(['a.order_id', 'c.store_name', 'c.store_avatar', 'a.order_state']);
        return $data->isEmpty() ? [] : $data->toArray();
    }

    static function getRefundStateOrder($member_id)
    {
        $data = DB::table('order as a')
            ->leftJoin('order_common as b', 'a.order_id', 'b.order_id')
            ->leftJoin('store as c', 'a.store_id', 'c.store_id')
            ->where('a.buyer_id', $member_id)
            ->where('refund_state', 2)
            ->get(['a.order_id', 'c.store_name', 'c.store_avatar', 'a.order_state']);
        return $data->isEmpty() ? [] : $data->toArray();
    }

    static function getBLGoodsMarketprice($bl_id)
    {
        return DB::table('goods AS a')
            ->leftJoin('p_bundling_goods AS b', 'a.goods_id', 'b.goods_id')
            ->where('b.bl_id', $bl_id)
            ->sum('a.goods_marketprice');
    }

    static function getCartInfoByStoreId($store_id, $member_id)
    {
        $field = ['cart_id', 'goods_id', 'goods_name', 'goods_price', 'goods_image', 'bl_id', 'xs_id', 'goods_num'];
        $data = BModel::getTableAllData('cart', ['store_id' => $store_id, 'buyer_id' => $member_id], $field);
        $amount = 0;
        if (!$data->isEmpty()) {
            foreach ($data as $k => &$v) {
                $amount += $v->goods_num * $v->goods_price;
                if ($v->bl_id != 0) {
                    $goods_data = BModel::getTableAllData('p_bundling_goods', ['bl_id' => $v->bl_id], ['goods_id']);
                    if ($goods_data->isEmpty()) {
                        return [];
                    }
                    $goods_id_array = [];
                    foreach ($goods_data as $i) {
                        array_push($goods_id_array, $i->goods_id);
                    }
                    $v->goods_marketprice = DB::table('goods')->whereIn('goods_id', array_unique($goods_id_array))->sum('goods_marketprice');
                    unset($v->bl_id);
                    unset($v->xs_id);
                } elseif ($v->xs_id != 0) {
                    $goods_data = BModel::getTableAllData('p_xianshi_goods', ['xianshi_id' => $v->xs_id], ['goods_id']);
                    if ($goods_data->isEmpty()) {
                        return [];
                    }
                    $goods_id_array = [];
                    foreach ($goods_data as $i) {
                        array_push($goods_id_array, $i->goods_id);
                    }
                    $v->goods_marketprice = DB::table('goods')->whereIn('goods_id', array_unique($goods_id_array))->sum('goods_marketprice');
                    unset($v->xs_id);
                    unset($v->bl_id);
                } else {
                    unset($v->bl_id);
                    unset($v->xs_id);
                    $v->goods_marketprice = BModel::getTableValue('goods', ['goods_id' => $v->goods_id], 'goods_marketprice');
                }
            }

        }
        $result['amount'] = BaseController::ncPriceFormat($amount);
        $result['data'] = $data;
        return $result;
    }
    static function checkExist($table,$condition)
    {
        return BModel::getCount($table,$condition)==0?false:true;
    }




}
