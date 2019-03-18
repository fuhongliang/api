<?php
namespace app\v1\controller;
use app\v1\controller\Base;
use think\Request;
use app\v1\model\Goods as GoodsModel;
use app\v1\model\Store as StoreModel;
/**
 * Class Goods 商品
 * @package app\v1\controller
 */
class Goods extends Base
{
    public function addGoods(Request $request)
    {
        $store_id=$request->param('store_id');
        $class_id=$request->param('class_id');//分类
        $goods_name=$request->param('goods_name');//名称
        $goods_price=$request->param('goods_price');//价格
        $origin_price=$request->param('origin_price');//原价
        $goods_storage=$request->param('goods_storage');//库存
        $sell_time=$request->param('sell_time'); // 出售时间
        $goods_desc=$request->param('goods_desc');// 描述

        if(!$store_id || !$class_id || !$goods_name || !$goods_price || !$origin_price || !$goods_storage || !$sell_time || !$goods_desc)
        {
            return Base::jsonReturn(1000,[],'参数缺失');
        }
        $bind_class=StoreModel::getStoreBindClass(['store_id'=>$store_id], ['class_1,class_2,class_3']);
        $common_array=array();
        $common_array['goods_name']         = $goods_name;
        $common_array['goods_jingle']       = '';
        $common_array['gc_id']              = intval($bind_class['class_3']);
        $common_array['gc_id_1']            = intval($bind_class['class_1']);
        $common_array['gc_id_2']            = intval($bind_class['class_2']);
        $common_array['gc_id_3']            = intval($bind_class['class_3']);
        $common_array['gc_name']            = 'gc_name';
        $common_array['brand_id']           = '';
        $common_array['brand_name']         = '';
        $common_array['type_id']            = 0;
        $common_array['goods_image']        = '';
        $common_array['goods_price']        = floatval($goods_price);
        $common_array['goods_marketprice']  = floatval($origin_price);
        $common_array['goods_costprice']    = floatval($goods_price);
        $common_array['goods_discount']     = 0;
        $common_array['goods_serial']       = 0;
        $common_array['goods_storage_alarm']= 0;
        $common_array['goods_attr']         = '';
        $common_array['goods_body']         = $goods_desc;

        $m_body = str_replace('&quot;', '"', $goods_desc);
        $m_body = json_decode($m_body, true);
        $mobile_body = serialize($m_body);

        $common_array['mobile_body']        = $mobile_body;
        $common_array['goods_commend']      = 0;
        $common_array['goods_state']        = 1;            // 店铺关闭时，商品下架
        $common_array['goods_addtime']      = TIMESTAMP;
        $common_array['goods_selltime']     = strtotime($_POST['starttime']) + intval($_POST['starttime_H'])*3600 + intval($_POST['starttime_i'])*60;
        $common_array['goods_verify']       = (C('goods_verify') == 1) ? 10 : 1;
        $common_array['store_id']           = $_SESSION['store_id'];
        $common_array['store_name']         = $_SESSION['store_name'];
        $common_array['spec_name']          = is_array($_POST['spec']) ? serialize($_POST['sp_name']) : serialize(null);
        $common_array['spec_value']         = is_array($_POST['spec']) ? serialize($_POST['sp_val']) : serialize(null);
        $common_array['goods_vat']          = intval($_POST['g_vat']);
        $common_array['areaid_1']           = intval($_POST['province_id']);
        $common_array['areaid_2']           = intval($_POST['city_id']);
        $common_array['transport_id']       = ($_POST['freight'] == '0') ? '0' : intval($_POST['transport_id']); // 售卖区域
        $common_array['transport_title']    = $_POST['transport_title'];
        $common_array['goods_freight']      = floatval($_POST['g_freight']);

        $common_array['goods_stcids'] = ','.$class_id.',';// 首尾需要加,

        $common_array['plateid_top']        = intval($_POST['plate_top']) > 0 ? intval($_POST['plate_top']) : '';
        $common_array['plateid_bottom']     = intval($_POST['plate_bottom']) > 0 ? intval($_POST['plate_bottom']) : '';
        $common_array['is_virtual']         = intval($_POST['is_gv']);
        $common_array['virtual_indate']     = $_POST['g_vindate'] != '' ? (strtotime($_POST['g_vindate']) + 24*60*60 -1) : 0;  // 当天的最后一秒结束
        $common_array['virtual_limit']      = intval($_POST['g_vlimit']) > 10 || intval($_POST['g_vlimit']) < 0 ? 10 : intval($_POST['g_vlimit']);
        $common_array['virtual_invalid_refund'] = intval($_POST['g_vinvalidrefund']);
        $common_array['is_fcode']           = intval($_POST['is_fc']);
        $common_array['is_appoint']         = intval($_POST['is_appoint']);     // 只有库存为零的商品可以预约
        $common_array['appoint_satedate']   = $common_array['is_appoint'] == 1 ? strtotime($_POST['g_saledate']) : '';   // 预约商品的销售时间
        $common_array['is_presell']         = $common_array['goods_state'] == 1 ? intval($_POST['is_presell']) : 0;     // 只有出售中的商品可以预售
        $common_array['presell_deliverdate']= $common_array['is_presell'] == 1? strtotime($_POST['g_deliverdate']) : ''; // 预售商品的发货时间
        $common_array['is_own_shop']        = in_array($_SESSION['store_id'], model('store')->getOwnShopIds()) ? 1 : 0;


        $goods_com_data=array();

    }









}
