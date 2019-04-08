<?php

namespace App\Http\Controllers\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController as Base;

use App\model\V2\Goods;
use App\model\V2\Store;
use App\model\V2\Voucher;


class VoucherController extends Base
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
            return Base::jsonReturn(1000,  '参数缺失');
        }
        if(empty($class_id)) {
            $stcId = Store::getStoreClassStcId(['store_id' => $store_id], ['stc_id']);
            $class_id=$stcId->stc_id;
        }
        $result=array();
        $result['class_list']=Store::getAllStoreClass(['store_id'=>$store_id],['stc_id','stc_name']);
        $result['goods_list']=Store::getStoreGoodsListByStcId($store_id,$class_id);
        return Base::jsonReturn(200,  '获取成功',$result);
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
            return Base::jsonReturn(1000,  '参数缺失');
        }
        $res=Goods::changeGoodsState($goods_id,$store_id);
        if ($res) {
            return Base::jsonReturn(200,  '设置成功');
        } else {
            return Base::jsonReturn(2000,  '设置失败');
        }

    }

    /** 添加商品
     * @param \App\Http\Controllers\V2\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addGoods(Request $request)
    {
        $store_id=$request->input('store_id');
        $class_id=$request->input('class_id');//分类
        $goods_name=$request->input('goods_name');//名称
        $goods_price=$request->input('goods_price');//价格
        $origin_price=$request->input('origin_price');//原价
        $goods_storage=$request->input('goods_storage');//库存
        $sell_time=$request->input('sell_time'); // 出售时间
        $goods_desc=$request->input('goods_desc');// 描述
        $goods_image=$request->file('goods_image');// 描述


        if(!$store_id || !$class_id || !$goods_name || !$goods_price || !$origin_price)
        {
            return Base::jsonReturn(1000,'参数缺失');
        }
        if(!$goods_storage)
        {
            $goods_storage=999999999;
        }
        $file_name=Base::getSysSetPath();
        $save_path = '/shop/store/goods' . '/' . $store_id . '/' . $file_name;
        $image_path = $request->file('goods_image')->store($save_path);
        $bind_class=Store::getStoreBindClass(['store_id'=>$store_id], ['class_1','class_2','class_3']);

        $common_array=array();
        //商品common信息
        $common_array['goods_name']         = $goods_name;
        $common_array['goods_jingle']       = '';
        $common_array['gc_id']              = intval($bind_class->class_3);
        $common_array['gc_id_1']            = intval($bind_class->class_1);
        $common_array['gc_id_2']            = intval($bind_class->class_2);
        $common_array['gc_id_3']            = intval($bind_class->class_3);
        $common_array['gc_name']            = '';
        $common_array['brand_id']           = 1;
        $common_array['brand_name']         = '';
        $common_array['type_id']            = 0;
        $common_array['goods_image']        =  $file_name;
        $common_array['goods_price']        = floatval($goods_price);
        $common_array['goods_marketprice']  = floatval($origin_price);
        $common_array['goods_costprice']    = floatval($goods_price);
        $common_array['goods_discount']     = 0;
        $common_array['goods_serial']       = 0;
        $common_array['goods_storage_alarm']= 0;
        $common_array['goods_attr']         = '';
        $common_array['goods_body']         = empty($goods_desc)? "":$goods_desc;
        $m_body = str_replace('&quot;', '"', $goods_desc);
        $m_body = json_decode($m_body, true);
        $mobile_body = serialize($m_body);
        $common_array['mobile_body']        = $mobile_body;
        $common_array['goods_commend']      = 0;
        $common_array['goods_state']        = 1;            // 店铺关闭时，商品下架
        $common_array['goods_addtime']      = time();
        $common_array['goods_verify']       = 1;
        $common_array['store_id']           = $store_id;
        $common_array['store_name']         = '';
        $common_array['spec_name']          = '';
        $common_array['spec_value']         = '';
        $common_array['goods_specname']     ='';
        $common_array['goods_vat']          = 0;
        $common_array['areaid_1']           = 0;
        $common_array['areaid_2']           = 0;
        $common_array['transport_id']       = 0; // 售卖区域
        $common_array['transport_title']    = 0;
        $common_array['goods_freight']      = 0;
        $common_array['goods_stcids'] = ','.$class_id.',';// 首尾需要加,
        $common_array['plateid_top']        =  1;
        $common_array['plateid_bottom']     =  1;
        $common_array['is_virtual']         = 0;
        $common_array['virtual_indate']     = 0;  // 当天的最后一秒结束
        $common_array['virtual_limit']      = 0;
        $common_array['virtual_invalid_refund'] = 0;
        $common_array['is_fcode']           = 0;
        $common_array['is_appoint']         = 0;     // 只有库存为零的商品可以预约
        $common_array['appoint_satedate']   = time();   // 预约商品的销售时间
        $common_array['is_presell']         = 0;     // 只有出售中的商品可以预售
        $common_array['presell_deliverdate']= time(); // 预售商品的发货时间
        $common_array['is_own_shop']        = 0;

        $selltime=array(
            array(
                'start_time'=>'00:00',
                'end_time'=>'23:59'
            )
        );
        foreach ($selltime as $k=>$val)
        {
            $goods_sell_time[$k][intval($val['start_time'])]=$val['end_time'];
        }
        $common_array['goods_sale_time']        = serialize($selltime);
        $common_array['goods_selltime']    = time();
        $common_id=Goods::addGoodsCommon($common_array);
/////  商品信息
        $goods = array();
        $goods['goods_commonid']    = $common_id;
        $goods['goods_name']        = $common_array['goods_name'];
        $goods['goods_jingle']      = $common_array['goods_jingle'];
        $goods['store_id']          = $common_array['store_id'];
        $goods['store_name']        = '';
        $goods['gc_id']             = $common_array['gc_id'];
        $goods['gc_id_1']           = $common_array['gc_id_1'];
        $goods['gc_id_2']           = $common_array['gc_id_2'];
        $goods['gc_id_3']           = $common_array['gc_id_3'];
        $goods['brand_id']          = $common_array['brand_id'];
        $goods['goods_price']       = $common_array['goods_price'];
        $goods['goods_promotion_price']=$common_array['goods_price'];
        $goods['goods_marketprice'] = $common_array['goods_marketprice'];
        $goods['goods_serial']      = $common_array['goods_serial'];
        $goods['goods_storage_alarm']= $common_array['goods_storage_alarm'];
        $goods['goods_spec']        = serialize(null);
        $goods['goods_storage']     = intval($goods_storage);
        $goods['goods_image']       = $common_array['goods_image'];
        $goods['goods_state']       = $common_array['goods_state'];
        $goods['goods_verify']      = $common_array['goods_verify'];
        $goods['goods_addtime']     = time();
        $goods['goods_edittime']    = time();
        $goods['areaid_1']          = $common_array['areaid_1'];
        $goods['areaid_2']          = $common_array['areaid_2'];
        $goods['color_id']          = 0;
        $goods['transport_id']      = $common_array['transport_id'];
        $goods['goods_freight']     = $common_array['goods_freight'];
        $goods['goods_vat']         = $common_array['goods_vat'];
        $goods['goods_commend']     = $common_array['goods_commend'];
        $goods['goods_stcids']      = $common_array['goods_stcids'];
        $goods['is_virtual']        = $common_array['is_virtual'];
        $goods['virtual_indate']    = $common_array['virtual_indate'];
        $goods['virtual_limit']     = $common_array['virtual_limit'];
        $goods['virtual_invalid_refund'] = $common_array['virtual_invalid_refund'];
        $goods['is_fcode']          = $common_array['is_fcode'];
        $goods['is_appoint']        = $common_array['is_appoint'];
        $goods['is_presell']        = $common_array['is_presell'];
        $goods['is_own_shop']       = $common_array['is_own_shop'];
        $goods_id = Goods::addGoods($goods);
        if($goods_id)
        {
            return Base::jsonReturn(200,'添加成功');
        }else{
            return Base::jsonReturn(2000,'添加失败');
        }

    }

    /**  添加代金券
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function voucherAdd(Request $request){
        $store_id=$request->input('store_id');
        $voucher_t_title=$request->input('title');
        $voucher_t_price=$request->input('mianzhi');
        $limit=$request->input('limit_price');
        $describe=$request->input('describe');
        $enddate=$request->input('end_time');
        $total=$request->input('total_nums');
        $eachlimit=$request->input('each_limit');
        if(!$store_id || !$voucher_t_title || !$voucher_t_price || !$limit || !$describe || !$enddate || !$total || !$eachlimit)
        {
            return Base::jsonReturn(1000,'参数缺失');
        }
        $where=[
            ['quota_storeid', '=', $store_id],
            ['quota_endtime', '>', time()],
        ];
        $quotainfo=Voucher::getVoucherQuotaInfo($where);
        if(empty($quotainfo)){
            return Base::jsonReturn(2000,  '你还没有购买代金券套餐');
        }

        $count=Voucher::getVoucherTemplateCount(['voucher_t_quotaid'=>$quotainfo->quota_id,'voucher_t_state'=>1]);
        if ($count >= getenv('PROMOTION_VOUCHER_STORETIMES_LIMIT')){
            return Base::jsonReturn(2000,  '代金券数量超过最多限制');
        }
        $pricelist=Voucher::getVoucherPriceList();
        if($pricelist->isEmpty())
        {
            return Base::jsonReturn(2000,  '没有可用面额');
        }
        $insert_arr['voucher_t_title'] = $voucher_t_title;
        $insert_arr['voucher_t_price'] = $voucher_t_price;
        $insert_arr['voucher_t_limit'] = $limit;
        $insert_arr['voucher_t_desc'] = $describe;
        $insert_arr['voucher_t_start_date'] = time();
        if ($enddate > $quotainfo->quota_endtime){
            $enddate = $quotainfo->quota_endtime;
        }
        $insert_arr['voucher_t_end_date'] = $enddate;
        $insert_arr['voucher_t_store_id'] = $store_id;
        $insert_arr['voucher_t_storename'] = $quotainfo->quota_storename;
        $insert_arr['voucher_t_sc_id'] = Store::getStoreField(['store_id'=>$store_id],'sc_id');
        $insert_arr['voucher_t_creator_id'] = $quotainfo->quota_memberid;
        $insert_arr['voucher_t_state'] = 1;
        $insert_arr['voucher_t_total'] = $total;
        $insert_arr['voucher_t_giveout'] = 0;
        $insert_arr['voucher_t_used'] = 0;
        $insert_arr['voucher_t_add_date'] = time();
        $insert_arr['voucher_t_quotaid'] = $quotainfo->quota_id ? $quotainfo->quota_id : 0;
        $insert_arr['voucher_t_points'] = 0;
        $insert_arr['voucher_t_eachlimit'] = $eachlimit;
        $res=Voucher::addVoucherTemplate($insert_arr);
        if ($res) {
            return Base::jsonReturn(200, '添加成功');
        } else {
            return Base::jsonReturn(2000,  '添加失败');
        }
    }




}
