<?php

namespace App\model\V3;

use App\BModel;
use App\Http\Controllers\BaseController as Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Redis;

class Store extends BModel
{
    /**
     * @param $condition
     * @return mixed
     */
    static function getStoreInfo($condition)
    {
        return BModel::getTableFirstData('store', $condition);
    }

    /**
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function getStoreAndJoinInfo($condition, $field = ['*'])
    {
        return DB::table('store as a')
            ->where($condition)
            ->leftJoin('store_joinin as b', 'a.member_id', 'b.member_id')
            ->leftJoin('member as c', 'a.member_id', 'c.member_id')
            ->get($field)
            ->first();
    }

    /**
     * @param $store_id
     * @param $class_name
     * @return bool
     */
    static function checkStoreGoodsClassExist($store_id, $class_name)
    {
        $data = DB::table('store_goods_class')
            ->where('store_id', $store_id)
            ->where('stc_name', $class_name)
            ->first();
        return empty($data) ? true : false;
    }

    /**
     * @param $ins_data
     * @return int
     */
    static function addStoreGoodsClass($ins_data)
    {
        return BModel::insertData('store_goods_class', $ins_data);
    }

    /**
     * @param $condition
     * @param array $field
     * @return \Illuminate\Support\Collection
     */
    static function getAllStoreClass($condition, $field = ['*'])
    {
        return DB::table('store_goods_class')
            ->where($condition)
            ->orderBy('stc_sort', 'asc')
            ->get($field);
    }

    /**
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function getStoreClassInfo($condition, $field = ['*'])
    {
        return BModel::getTableFieldFirstData('store_goods_class', $condition, $field);
    }

    /**
     * @param $condition
     * @param $up_data
     * @return int
     */
    static function editStoreClassInfo($condition, $up_data)
    {
        return BModel::upTableData('store_goods_class', $condition, $up_data);
    }

    /**
     * @param $class_id
     * @param $store_id
     * @return bool
     */
    static function delStoreClassInfo($class_id, $store_id)
    {
        DB::transaction(function () use ($class_id, $store_id) {
            BModel::delData('store_goods_class', ['stc_id' => $class_id, 'store_id' => $store_id]);
            BModel::delData('goods', ['goods_stcid' => $class_id]);
            BModel::delData('goods_common', ['goods_stcid' => $class_id]);
        });
        return true;
    }

    /**
     * @param $class_ids
     * @param $store_id
     * @return bool
     */
    static function sortStoreGoodsClass($class_ids, $store_id)
    {
        if (!empty($class_ids)) {
            foreach ($class_ids as $k => $id) {
                self::upStoreGoodsClassSort(['stc_id' => $id, 'store_id' => $store_id], ['stc_sort' => $k]);
            }
            return true;
        }
        return false;
    }

    /**
     * @param $condition
     * @param $up_data
     * @return int
     */
    static function upStoreGoodsClassSort($condition, $up_data)
    {
        return BModel::upTableData('store_goods_class', $condition, $up_data);
    }

    /**
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function getStoreClassStcId($condition, $field = ['*'])
    {
        $res = DB::table('store_goods_class')
            ->where($condition)
            ->orderBy('stc_sort', 'asc')
            ->get($field)
            ->first();
        return $res;
    }

    /**
     * @param $store_id
     * @param $class_id
     * @return array
     */
    static function getStoreGoodsListByStcId($store_id, $class_id)
    {
        $goods_info = $ids = array();
        $data       = DB::table('goods')
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
                $xianshi      = [];
                $xianshi_data = BModel::getTableAllData('p_xianshi_goods', ['store_id' => $store_id], ['xianshi_goods_id', 'xianshi_price']);
                if (!$xianshi_data->isEmpty()) {
                    foreach ($xianshi_data as $k => $val) {
                        $xianshi[$val->xianshi_goods_id] = $val->xianshi_price;
                    }
                }
                foreach ($ids as $k => $goods_id) {
                    $fields         = ['a.goods_id', 'a.goods_name', 'a.goods_price', 'a.goods_marketprice', 'b.goods_body as goods_desc', 'b.goods_sale_time', 'a.goods_state','is_much', 'a.goods_storage', 'a.goods_image as img_name'];
                    $goods_info[$k] = Goods::getGoodsInfo(['goods_id' => $goods_id], $fields);
                    if($goods_info[$k]->is_much ==2)
                    {
                        $goods_info[$k]->goods_storage="库存无限";
                    }
                    $goods_info[$k]->goods_sale_time = unserialize($goods_info[$k]->goods_sale_time);
                    if (array_key_exists($goods_id, $xianshi)) {
                        $goods_info[$k]->xianshi_price = $xianshi[$goods_id];
                    } else {
                        $goods_info[$k]->xianshi_price = "";
                    }
                }
            }
            return $goods_info;
        }
    }

    /**
     * @param $store_id
     * @param $class_id
     * @return array
     */
    static function getXianshiGoodsList($store_id, $class_id)
    {
        $goods_info = $ids = array();
        $arr        = BModel::getTableAllData('p_xianshi_goods', ['store_id' => $store_id], ['goods_id']);
        if ($arr->isEmpty()) {
            $data = DB::table('goods')
                ->where('store_id', $store_id)
                ->whereNotNull('goods_stcids')
                ->get(['goods_id', 'goods_stcids']);
        } else {
            $arr_goods_ids = [];
            foreach ($arr as $v) {
                $arr_goods_ids[] = $v->goods_id;
            }
            $data = DB::table('goods')
                ->where('store_id', $store_id)
                ->whereNotNull('goods_stcids')
                ->whereNotIn('goods_id', $arr_goods_ids)
                ->get(['goods_id', 'goods_stcids']);
        }
        if (empty($data)) {
            return $goods_info;
        } else {
            foreach ($data as $val) {
                if (!empty($val->goods_stcids)) {
                    $stcids = explode(',', $val->goods_stcids);
                    if (in_array($class_id, $stcids)) {
                        array_push($ids, $val->goods_id);
                    }
                }
            }
            if (!empty($ids)) {
                foreach ($ids as $k => $goods_id) {
                    $fields         = ['a.goods_id', 'a.goods_name', 'a.goods_price', 'a.goods_marketprice', 'b.goods_body as goods_desc', 'b.goods_sale_time', 'a.goods_state', 'a.goods_storage', 'a.goods_image as img_name'];
                    $goods_info[$k] = Goods::getGoodsInfo(['goods_id' => $goods_id], $fields);
                    //$goods_info[$k]->img_path=getenv("GOODS_IMAGE").$store_id;
                    $goods_info[$k]->goods_sale_time = unserialize($goods_info[$k]->goods_sale_time);
                }
            }
            return $goods_info;
        }
    }

    /**
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function getStoreBindClass($condition, $field = ['*'])
    {
        return BModel::getTableFieldFirstData('store_bind_class', $condition, $field);
    }

    /**
     * @param $condition
     * @param array $field
     * @return array
     */
    static function getStoreData($condition, $field = ['*'])
    {
        $result = array();
        $data   = DB::table('store as a')
            ->leftJoin('store_joinin as b', 'a.member_id', 'b.member_id')
            ->where($condition)
            ->get($field)
            ->first();
        if (!empty($data)) {
            $result['store_state'] = self::getStoreState($data->store_state);
            $result['store_desc']  = $data->store_description;
            $result['store_logo']  = $data->store_label;
            $result['store_phone'] = $data->store_phone;
            $result['address']     = $data->area_info . $data->store_address;
            $result['store_zizhi'] = $data->business_licence_number_electronic;
        }
        return $result;
    }

    /**
     * @param $state
     * @return string
     */
    static function getStoreState($state)
    {
        if ($state == 0) {
            return '关闭中';
        } elseif ($state == 1) {
            return '开启中';
        } else {
            return '审核中';
        }
    }

    /**
     * @param $condition
     * @param $up_data
     * @return int
     */
    static function setWorkState($condition, $up_data)
    {
        return BModel::upTableData('store', $condition, $up_data);
    }

    /**
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function getStoreMemInfo($condition, $field = ['*'])
    {
        return DB::table('store as a')
            ->where($condition)
            ->leftJoin('member as c', 'a.member_id', 'c.member_id')
            ->get($field)
            ->first();
    }

    /**
     * @param $data
     * @return int
     */
    static function addAppFeedBack($data)
    {
        return BModel::insertData('mb_feedback', $data);
    }

    /**
     * @param $store_id
     * @return array
     */
    static function getComNums($store_id)
    {
        $result              = array();
        $result['all']       = self::getPingNumsByType(['store_id' => $store_id]);
        $result['haoping']   = self::getPingNumsByType(['store_id' => $store_id, 'haoping' => 1]);
        $result['zhongping'] = self::getPingNumsByType(['store_id' => $store_id, 'haoping' => 2]);
        $result['chaping']   = self::getPingNumsByType(['store_id' => $store_id, 'haoping' => 3]);
        $result['rate']      = 0;
        if ($result['all'] !== 0) {
            $result['rate'] = ceil($result['haoping'] / $result['all']);
        }
        return $result;
    }

    /**
     * @param $condition
     * @return int
     */
    static function getPingNumsByType($condition)
    {
        return BModel::getCount('store_com', $condition);
    }

    /**
     * @param $condition
     * @return array
     */
    static function getStoreComAllData($condition)
    {
        $result = array();
        $data   = DB::table('store_com as a')
            ->where($condition)
            ->leftJoin('member as b', 'a.member_id', 'b.member_id')
            ->get(['a.*', 'b.member_avatar', 'b.member_name']);
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $result[$k]['com_id']        = $v->com_id;
                $result[$k]['content']       = $v->content;
                $result[$k]['haoping']       = $v->haoping;
                $result[$k]['kouwei']        = $v->kouwei;
                $result[$k]['baozhuang']     = $v->baozhuang;
                $result[$k]['peisong']       = $v->peisong;
                $result[$k]['add_time']      = date('Y-m-d H:i:s', $v->add_time);
                $result[$k]['member_avatar'] = $v->member_avatar;
                $result[$k]['member_name']   = $v->member_name;
                $result[$k]['replay']        = null;
                if ($v->is_replay == 1) {
                    $result[$k]['replay'] = self::getComReplay(['parent_id' => $v->com_id]);
                }
            }
        }
        return $result;

    }

    /**
     * @param $condition
     * @return mixed
     */
    static function getComReplay($condition)
    {
        return BModel::getTableValue('store_com', $condition, 'content');
    }

    /**
     * @param $data
     * @return int
     */
    static function addStoreCom($data)
    {
        return BModel::insertData('store_com', $data);
    }

    /**
     * @param $condition
     * @param $up_data
     * @return int
     */
    static function upStoreCom($condition, $up_data)
    {
        return BModel::upTableData('store_com', $condition, $up_data);
    }

    /**
     * @param $condition
     * @param $field
     * @return mixed
     */
    static function getStoreField($condition, $field)
    {
        return BModel::getTableValue('store', $condition, $field);
    }

    /**
     * @param $member_id
     * @return string
     */
    static function makeSn($member_id)
    {
        return mt_rand(10, 99)
            . sprintf('%010d', time() - 946656000)
            . sprintf('%03d', (float)microtime() * 1000)
            . sprintf('%03d', (int)$member_id % 1000);
    }

    /**
     * @return array
     */
    static function getAreaList()
    {
        $data = BModel::getTableAllData('area', ['area_parent_id' => 0], ['area_id as id', 'area_name as province'])->toArray();
        foreach ($data as $key => &$val) {
            $val->children = BModel::getTableAllData('area', ['area_parent_id' => $val->id], ['area_id as id', 'area_name as city'])->toArray();
            foreach ($val->children as &$v) {
                $v->children = BModel::getTableAllData('area', ['area_parent_id' => $val->id], ['area_id as id', 'area_name as area'])->toArray();
            }
        }
        return $data;
    }

    static function getGcList()
    {
        $data = BModel::getTableAllData('goods_class', ['gc_parent_id' => 0], ['gc_id', 'gc_name'])->toArray();
        foreach ($data as $key => &$val) {
            $val->children = BModel::getTableAllData('goods_class', ['gc_parent_id' => $val->gc_id], ['gc_id', 'gc_name'])->toArray();
            foreach ($val->children as &$v) {
                $v->children = BModel::getTableAllData('goods_class', ['gc_parent_id' => $val->gc_id], ['gc_id', 'gc_name'])->toArray();
            }
        }
        return $data;
    }

    static function getmsgInfo($condition)
    {
        return DB::table('store_msg as a')
            ->leftJoin('store_msg_tpl as b', 'a.smt_code', 'b.smt_code')
            ->where($condition)
            ->first(['a.sm_id', 'a.sm_content', 'a.sm_addtime', 'b.smt_name as sm_title']);
    }

    static function msgList($condition, $page)
    {
        $result = [];
        $skip   = ($page - 1) * 10;
        $count  = BModel::getCount('store_msg', $condition);
        $total  = intval(ceil($count / 10));
        if ($page > $total) {
            return $result;
        }
        $data = DB::table('store_msg')
            ->where($condition)
            ->orderBy('sm_id', 'desc')
            ->skip($skip)
            ->take(10)
            ->get(['sm_id', 'sm_content', 'sm_addtime']);
        if (!$data->isEmpty()) {
            foreach ($data as $k => $v) {
                $result[$k]['sm_id']      = $v->sm_id;
                $result[$k]['sm_content'] = $v->sm_content;
                $result[$k]['sm_addtime'] = date('Y-m-d H:i:s', $v->sm_addtime);
            }
        }
        return $result;
    }

    static function cashList($member_id,$begin_time,$end_time,$field)
    {
       return  DB::table('pd_cash')
           ->where('pdc_member_id',$member_id)
           ->whereBetween('pdc_add_time',[$begin_time,$end_time])
           ->orderBy('pdc_id','desc')
           ->get($field);
    }

    static function getCashSum($member_id,$begin_time,$end_time,$field)
    {
        return   DB::table('pd_cash')->where('pdc_member_id',$member_id)->whereBetween('pdc_add_time',[$begin_time,$end_time])->sum($field);
    }
}
