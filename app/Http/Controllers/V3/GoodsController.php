<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\BaseController as Base;
use App\model\V3\Goods;
use App\model\V3\Store;
use App\model\V3\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Qiniu\Config;
use zgldh\QiniuStorage\QiniuStorage;


class GoodsController extends Base
{
    /** 商品列表  第二版
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeGoodsList(Request $request)
    {
        $class_id = $request->input('class_id');
        $store_id = $request->input('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $result = array();
        if (empty($class_id)) {
            $stcId = Store::getStoreClassStcId(['store_id' => $store_id], ['stc_id']);
            if (!empty($stcId)) {
                $class_id = $stcId->stc_id;
            } else {
                $result['class_list'] = $result['goods_list'] = null;
                return Base::jsonReturn(200, '获取成功', $result);
            }
        }

        $result['class_list'] = Store::getAllStoreClass(['store_id' => $store_id], ['stc_id', 'stc_name']);
        $result['goods_list'] = Store::getStoreGoodsListByStcId($store_id, $class_id);
        return Base::jsonReturn(200, '获取成功', $result);
    }

    public function xianshiGoodsList(Request $request)
    {
        $class_id = $request->input('class_id');
        $store_id = $request->input('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        if (empty($class_id)) {
            $stcId    = Store::getStoreClassStcId(['store_id' => $store_id], ['stc_id']);
            $class_id = $stcId->stc_id;
        }
        $result               = array();
        $result['class_list'] = Store::getAllStoreClass(['store_id' => $store_id], ['stc_id', 'stc_name']);
        $result['goods_list'] = Store::getXianshiGoodsList($store_id, $class_id);
        return Base::jsonReturn(200, '获取成功', $result);
    }

    /** 商品上下架  第二版
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeGoodsState(Request $request)
    {
        $goods_id = $request->input('goods_id');
        $store_id = $request->input('store_id');
        if (empty($store_id) || empty($goods_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2001, '商家不存在');
        }
        $res = Goods::changeGoodsState($goods_id, $store_id);
        if ($res == 1) {
            return Base::jsonReturn(200, '上架成功');
        } elseif ($res == 0) {
            return Base::jsonReturn(200, '下架成功');
        } else {
            return Base::jsonReturn(2000, '操作失败');
        }

    }

    /** 添加商品
     * @param \App\Http\Controllers\V3\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addGoods(Request $request)
    {
        $store_id      = $request->input('store_id');
        $class_id      = $request->input('class_id');//分类
        $goods_name    = $request->input('goods_name');//名称
        $goods_price   = Base::ncPriceFormat($request->input('goods_price'));//价格
        $origin_price  = Base::ncPriceFormat($request->input('origin_price'));//原价
        $goods_storage = $request->input('goods_storage');//库存
        $sell_time     = $request->input('sell_time'); // 出售时间
        $goods_desc    = $request->input('goods_desc');// 描述
        $file_name     = $request->input('img_name');//

        if (!$store_id || !$class_id || !$goods_name || !$goods_price || !$origin_price || !$file_name) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        if (!$goods_storage) {
            $goods_storage = 999999999;
        }
        $bind_class = Store::getStoreBindClass(['store_id' => $store_id], ['class_1', 'class_2', 'class_3']);

        $common_array = array();
        //商品common信息
        $common_array['goods_name']             = $goods_name;
        $common_array['goods_jingle']           = '';
        $common_array['gc_id']                  = intval($bind_class->class_3);
        $common_array['gc_id_1']                = intval($bind_class->class_1);
        $common_array['gc_id_2']                = intval($bind_class->class_2);
        $common_array['gc_id_3']                = intval($bind_class->class_3);
        $common_array['gc_name']                = '';
        $common_array['brand_id']               = 1;
        $common_array['brand_name']             = '';
        $common_array['type_id']                = 0;
        $common_array['goods_image']            = $file_name;
        $common_array['goods_price']            = floatval($goods_price);
        $common_array['goods_marketprice']      = floatval($origin_price);
        $common_array['goods_costprice']        = floatval($goods_price);
        $common_array['goods_discount']         = 0;
        $common_array['goods_serial']           = 0;
        $common_array['goods_storage_alarm']    = 0;
        $common_array['goods_attr']             = '';
        $common_array['goods_body']             = empty($goods_desc) ? "" : $goods_desc;
        $m_body                                 = str_replace('&quot;', '"', $goods_desc);
        $m_body                                 = json_decode($m_body, true);
        $mobile_body                            = serialize($m_body);
        $common_array['mobile_body']            = $mobile_body;
        $common_array['goods_commend']          = 0;
        $common_array['goods_state']            = 1;            // 店铺关闭时，商品下架
        $common_array['goods_addtime']          = time();
        $common_array['goods_verify']           = 1;
        $common_array['store_id']               = $store_id;
        $common_array['store_name']             = '';
        $common_array['spec_name']              = '';
        $common_array['spec_value']             = '';
        $common_array['goods_specname']         = '';
        $common_array['goods_vat']              = 0;
        $common_array['areaid_1']               = 0;
        $common_array['areaid_2']               = 0;
        $common_array['transport_id']           = 0; // 售卖区域
        $common_array['transport_title']        = 0;
        $common_array['goods_freight']          = 0;
        $common_array['goods_stcids']           = ',' . $class_id . ',';// 首尾需要加,
        $common_array['plateid_top']            = 1;
        $common_array['plateid_bottom']         = 1;
        $common_array['is_virtual']             = 0;
        $common_array['virtual_indate']         = 0;  // 当天的最后一秒结束
        $common_array['virtual_limit']          = 0;
        $common_array['virtual_invalid_refund'] = 0;
        $common_array['is_fcode']               = 0;
        $common_array['is_appoint']             = 0;     // 只有库存为零的商品可以预约
        $common_array['appoint_satedate']       = time();   // 预约商品的销售时间
        $common_array['is_presell']             = 0;     // 只有出售中的商品可以预售
        $common_array['presell_deliverdate']    = time(); // 预售商品的发货时间
        $common_array['is_own_shop']            = 0;
        $common_array['goods_stcid']            = $class_id;

        if (!$sell_time) {
            $sell_time = array(
                array(
                    'start_time' => '00:00',
                    'end_time' => '23:59'
                )
            );
        }
        foreach ($sell_time as $k => $val) {
            $goods_sell_time[$k]['start_time'] = $val['start_time'];
            $goods_sell_time[$k]['end_time']   = $val['end_time'];
        }
        $common_array['goods_sale_time'] = serialize($goods_sell_time);
        $common_array['goods_selltime']  = time();
        $common_id                       = Goods::addGoodsCommon($common_array);
/////  商品信息
        $goods                           = array();
        $goods['goods_commonid']         = $common_id;
        $goods['goods_name']             = $common_array['goods_name'];
        $goods['goods_jingle']           = $common_array['goods_jingle'];
        $goods['store_id']               = $common_array['store_id'];
        $goods['store_name']             = '';
        $goods['gc_id']                  = $common_array['gc_id'];
        $goods['gc_id_1']                = $common_array['gc_id_1'];
        $goods['gc_id_2']                = $common_array['gc_id_2'];
        $goods['gc_id_3']                = $common_array['gc_id_3'];
        $goods['brand_id']               = $common_array['brand_id'];
        $goods['goods_price']            = $common_array['goods_price'];
        $goods['goods_promotion_price']  = $common_array['goods_price'];
        $goods['goods_marketprice']      = $common_array['goods_marketprice'];
        $goods['goods_serial']           = $common_array['goods_serial'];
        $goods['goods_storage_alarm']    = $common_array['goods_storage_alarm'];
        $goods['goods_spec']             = serialize(null);
        $goods['goods_storage']          = intval($goods_storage);
        $goods['goods_image']            = $common_array['goods_image'];
        $goods['goods_state']            = $common_array['goods_state'];
        $goods['goods_verify']           = $common_array['goods_verify'];
        $goods['goods_addtime']          = time();
        $goods['goods_edittime']         = time();
        $goods['areaid_1']               = $common_array['areaid_1'];
        $goods['areaid_2']               = $common_array['areaid_2'];
        $goods['color_id']               = 0;
        $goods['transport_id']           = $common_array['transport_id'];
        $goods['goods_freight']          = $common_array['goods_freight'];
        $goods['goods_vat']              = $common_array['goods_vat'];
        $goods['goods_commend']          = $common_array['goods_commend'];
        $goods['goods_stcids']           = $common_array['goods_stcids'];
        $goods['is_virtual']             = $common_array['is_virtual'];
        $goods['virtual_indate']         = $common_array['virtual_indate'];
        $goods['virtual_limit']          = $common_array['virtual_limit'];
        $goods['virtual_invalid_refund'] = $common_array['virtual_invalid_refund'];
        $goods['is_fcode']               = $common_array['is_fcode'];
        $goods['is_appoint']             = $common_array['is_appoint'];
        $goods['is_presell']             = $common_array['is_presell'];
        $goods['is_own_shop']            = $common_array['is_own_shop'];
        $goods['goods_stcid']            = $class_id;
        $goods_id                        = Goods::addGoods($goods);
        if ($goods_id) {
            return Base::jsonReturn(200, '添加成功');
        } else {
            return Base::jsonReturn(2000, '添加失败');
        }

    }

    public function delGoods(Request $request)
    {
        $store_id = $request->input('store_id');
        $goods_id = $request->input('goods_id');//
        if (!$store_id || !$goods_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2001, '商家不存在');
        }
        $res = Goods::delGoods($store_id, $goods_id);
        if ($res) {
            return Base::jsonReturn(200, '删除成功');
        } else {
            return Base::jsonReturn(2000, '删除失败');
        }
    }

    public function getGoodsInfo(Request $request)
    {
        $store_id = $request->input('store_id');
        $goods_id = $request->input('goods_id');//
        if (!$store_id || !$goods_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2001, '商家不存在');
        }
        $field                  = ['goods_id', 'goods_commonid', 'goods_image', 'goods_name', 'goods_stcid', 'goods_marketprice', 'goods_price', 'goods_storage'];
        $goods_info             = Goods::getGoodsInfo(['store_id' => $store_id, 'goods_id' => $goods_id], $field);
        $goods_com              = Goods::getGoodsCommonInfo(['goods_commonid' => $goods_info->goods_commonid], ['goods_sale_time', 'goods_body']);
        $goods_info->goods_body = $goods_com->goods_body;
        $goods_info->sell_time  = unserialize($goods_com->goods_sale_time);
        //$goods_info->goods_image = getenv("GOODS_IMAGE") . $store_id . '/' . $goods_info->goods_image;

        if ($goods_info) {
            return Base::jsonReturn(200, '获取成功', $goods_info);
        } else {
            return Base::jsonReturn(2000, '获取失败');
        }
    }

    public function editGoods(Request $request)
    {
        $store_id      = $request->input('store_id');
        $goods_id      = $request->input('goods_id');
        $class_id      = $request->input('class_id');//分类
        $goods_name    = $request->input('goods_name');//名称
        $goods_price   = $request->input('goods_price');//价格
        $origin_price  = $request->input('origin_price');//原价
        $goods_storage = $request->input('goods_storage');//库存
        $sell_time     = $request->input('sell_time'); // 出售时间
        $goods_desc    = $request->input('goods_desc');// 描述
        $file_name     = $request->input('img_name');//
        if (!$goods_id || !$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $goods_array = $goods_comm = array();
        if ($class_id) {
            $goods_array['goods_stcid']  = $class_id;
            $goods_array['goods_stcids'] = ',' . $class_id . ',';
        }
        if ($goods_name) {
            $goods_array['goods_name'] = $goods_name;
            $goods_comm['goods_name']  = $goods_name;
        }
        if ($goods_price) {
            $goods_array['goods_price'] = $goods_price;
            $goods_comm['goods_price']  = $goods_price;
        }
        if ($origin_price) {
            $goods_array['goods_marketprice'] = $origin_price;
            $goods_comm['goods_marketprice']  = $origin_price;
        }
        if ($goods_storage) {
            $goods_array['goods_storage'] = $goods_storage;
        }
        if ($sell_time) {
            $goods_comm['goods_sale_time'] = serialize($sell_time);
        }
        if ($goods_desc) {
            $goods_comm['goods_body'] = $goods_desc;
        }
        if ($file_name) {
            $goods_array['goods_image'] = $file_name;
            $goods_comm['goods_image']  = $file_name;
        }
        $field = Goods::getGoodsInfo(['goods_id' => $goods_id], ['a.goods_commonid', 'a.goods_image']);
        if (!empty($field->goods_image)) {
            $file_name = $field->goods_image;
            $disk      = QiniuStorage::disk('qiniu');
            $disk->delete($file_name);
//            $img_path  = '/shop/store/goods' . '/' . $store_id . '/' . $file_name;
//            Storage::disk('public')->delete($img_path);
        }
        DB::transaction(function () use ($field, $goods_id, $goods_array, $goods_comm) {
            Goods::upGoodsField(['goods_id' => $goods_id], $goods_array);
            Goods::upGoodsCommonField(['goods_commonid' => $field->goods_commonid], $goods_comm);
        });
        return Base::jsonReturn(200, '编辑成功');
    }

    /**
     * @param Request $request
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     */
    function upImage(Request $request, $type)
    {
        $image = $request->file('file');
        $token = $request->header('token');
        if (!$image) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $filesize = $image->getClientSize();
        if ($filesize > 2048) {
            return Base::jsonReturn(2000, '图片大小超过2M限制');
        }
        $entension = $image->getClientOriginalExtension();
        $allow_ext = ['png', 'jpg', 'jpeg'];
        if (!in_array($entension, $allow_ext)) {
            return Base::jsonReturn(2001, '图片格式不允许');
        }
        $file_name = "";
//        $tokenInfo = Token::getTokenField(['token' => $token], ['store_id']);
//        $store_id  = $tokenInfo->store_id;
        $disk       = QiniuStorage::disk('qiniu');
        $image_name = $disk->put('', $image);
        if ($image_name) {
            // $img_url=getenv('QINIU_DOMAIN').$image_name;
            return Base::jsonReturn(200, '上传成功', $image_name);
        } else {
            return Base::jsonReturn(2000, '上传失败');
        }


    }

}
