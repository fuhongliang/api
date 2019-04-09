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
            return Base::jsonReturn(200,  '上下架成功');
        } else {
            return Base::jsonReturn(2000,  '上下架失败');
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

        if(!$store_id || !$class_id || !$goods_name || !$goods_price || !$origin_price || !$goods_image)
        {
            return Base::jsonReturn(1000,'参数缺失');
        }
        if(!$goods_storage)
        {
            $goods_storage=999999999;
        }

        $save_path = '/shop/store/goods' . '/' . $store_id  . Base::getSysSetPath();
        $entension = $goods_image -> getClientOriginalExtension();
        $file_name=md5(microtime()).'.'.$entension;
        $image_path = $request->file('goods_image')->storeAs(
            $save_path,$file_name
        );

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
        $common_array['goods_stcid']        = $class_id;

        if(!$sell_time)
        {
            $sell_time=array(
                array(
                    'start_time'=>'00:00',
                    'end_time'=>'23:59'
                )
            );
        }
        foreach ($sell_time as $k=>$val)
        {
            $goods_sell_time[$k][intval($val['start_time'])]=$val['end_time'];
        }
        $common_array['goods_sale_time']        = serialize($goods_sell_time);
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
        $goods['goods_stcid']        = $class_id;
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
    public function voucherList(Request $request){
        $store_id=$request->input('store_id');
        if(!$store_id)
        {
            return Base::jsonReturn(1000,'参数缺失');
        }
//查询是否存在可用套餐
        $where=[
            ['quota_storeid', '=', $store_id],
            ['quota_endtime', '>', time()],
        ];
        $quotainfo=Voucher::getVoucherQuotaInfo($where);
        if(empty($quotainfo))
        {
            return Base::jsonReturn(2000,  '你还没有购买代金券套餐');
        }
        $list=Voucher::getVoucherTemplateList(['voucher_t_store_id'=>$store_id]);
        if($list->isEmpty())
        {
            return Base::jsonReturn(200,'添加失败');
        }else{
            $result=array();
            foreach ($list as $k=>$v)
            {
                $result[$k]['voucher_id']=$v->voucher_t_id;
                $result[$k]['voucher_title']=$v->voucher_t_title;
                $result[$k]['voucher_eachlimit']=$v->voucher_t_eachlimit;
                $result[$k]['voucher_start_date']=date('Y-m-d H:i:s',$v->voucher_t_start_date);
                $result[$k]['voucher_end_date']=date('Y-m-d H:i:s',$v->voucher_t_end_date);
                $result[$k]['voucher_surplus']=$v->voucher_t_total-$v->voucher_t_giveout;//剩余
                $result[$k]['voucher_used']=$v->voucher_t_used;//使用
                $result[$k]['voucher_giveout']=$v->voucher_t_giveout;//领取
                $result[$k]['voucher_limit']=$v->voucher_t_limit;
                $result[$k]['voucher_state']=$v->voucher_t_state;
            }
            return Base::jsonReturn(200,'添加失败',$result);
        }
    }

    /** 代金券删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function voucherDel(Request $request)
    {
        $store_id = $request->input('store_id');
        $voucher_id = $request->input('voucher_id');
        if (!$store_id || !$voucher_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $res=Voucher::delVoucher(['voucher_t_store_id'=>$store_id,'voucher_t_id'=>$voucher_id]);
        if ($res) {
            return Base::jsonReturn(200, '删除成功');
        } else {
            return Base::jsonReturn(2000,  '删除失败');
        }
    }


    /** 添加优惠套装
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bundlingAdd(Request $request)
    {
        $store_id = $request->input('store_id');
        $bundling_name = $request->input('bundling_name');
        $bl_discount_price = $request->input('discount_price');


        $data=array(
            'store_id'=>$store_id,
            'bl_name'=>$bundling_name,
            'store_name'=>Store::getStoreField(['store_id'=>$store_id],'store_name'),
            'bl_discount_price'=>$bl_discount_price,
            'bl_freight_choose'=>1,
            'bl_freight'=>0,

        );
        $bundling_id=Voucher::addBundlingData($data);
        $goods_list=array(
            array(
                'goods_id'=>100058,
                'goods_price'=>333
            ),
            array(
                'goods_id'=>100066,
                'goods_price'=>200
            ),
        );
        foreach ($goods_list as $key => $val){
            $goods_info = Goods::getGoodsInfo(['goods_id'=>$val['goods_id']],['goods_id','goods_name','goods_image','store_id']);
            $array = array();
            $array['bl_id'] = $bundling_id;
            $array['goods_id'] = $val['goods_id'];
            $array['goods_name'] = $goods_info->goods_name;
            $array['goods_image'] = $goods_info->goods_image;
            $array['bl_goods_price'] = Base::ncPriceFormat($val['goods_price']);
            $array['bl_appoint'] = 1;
            Voucher::addBundlingGoodsData($array);
        }
        return Base::jsonReturn(200, '添加成功');

    }
    public function bundlingList(Request $request)
    {
        $store_id = $request->input('store_id');
        if(!$store_id)
        {
            return Base::jsonReturn(1000,'参数缺失');
        }
        $list= Voucher::getBundlingData(['store_id'=>$store_id],['bl_id','bl_name','bl_state']);
        if(!empty($list))
        {
            $result=array();
            foreach ($list as $k=>$v)
            {
                $result[$k]['bl_id']=$v->bl_id;
                $result[$k]['bl_name']=$v->bl_name;
                $result[$k]['bl_state']=$v->bl_state;
                $total_price=Voucher::getBundlingGoodsTotalPrice(['bl_id'=>$v->bl_id]);
                $result[$k]['price']=$total_price->price;
            }
            return Base::jsonReturn(200, '查询成功',$result);
        }else{
            return Base::jsonReturn(200, '查询成功');
        }
    }

    /** 删除优惠套装
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bundlingDel(Request $request)
    {
        $bundling_id = $request->input('bundling_id');
        $store_id = $request->input('store_id');
        if (!$bundling_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $res=Voucher::delBundling(['bl_id'=>$bundling_id]);
        if ($res) {
            return Base::jsonReturn(200, '删除成功');
        } else {
            return Base::jsonReturn(2000,  '删除失败');
        }
    }
    public function mamsongAdd(Request $request)
    {
        $store_id = $request->input('store_id');
        $mansong_name = $request->input('mansong_name');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $remark = $request->input('remark');
        if (!$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $mansong_quota_list=Voucher::getManSongInfo(['store_id'=>$store_id],['quota_id']);
        if(empty($mansong_quota_list))
        {
            return Base::jsonReturn(2000,  '你还没有购买套餐');
        }
        $storeInfo=Store::getStoreInfo(['store_id'=>$store_id]);
        $data=array(
            'mansong_name'=>$mansong_name,
            'start_time'=>$start_time,
            'end_time'=>$end_time,
            'quota_id'=>$mansong_quota_list->quota_id,
            'member_id'=>$storeInfo->member_id,
            'member_name'=>$storeInfo->member_name,
            'store_name'=>$storeInfo->store_name,
            'state'=>2,
            'remark'=>$remark
        );
        $mansong_id=Voucher::addManSongData($data);
        $rules=array(
            array(
               'price'=>100,
               'discount' =>10,
                'mansong_goods_name'=>''
            )
        );
        foreach($rules as $v)
        {
            $arr=array(
                'price'=>$v['price'],
                'discount' =>$v['discount'],
                'mansong_goods_name'=>''
            );
            Voucher::addManSongRuleData($arr);
        }
        return Base::jsonReturn(200,  '添加成功');
    }
    public function mamsongList(Request $request)
    {
        $store_id = $request->input('store_id');
        if (!$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $list=Voucher::getManSongList(['store_id'=>$store_id]);
        if(!empty($list))
        {
            $result=array();
            foreach ($list as $k=>$v)
            {
                $result[$k]['mansong_name']=$v->mansong_name;
                $result[$k]['start_time']=$v->start_time;
                $result[$k]['end_time']=$v->end_time;
                $result[$k]['state']=$v->state;
                $result[$k]['rule']=Voucher::getManSongRuleList(['mansong_id'=>$v->mansong_id],['price','discount']);
            }
            return Base::jsonReturn(200,  '获取成功',$result);
        }else{
            return Base::jsonReturn(200,  '获取成功');
        }
    }
    public function mansongDel(Request $request)
    {
        $mansong_id = $request->input('mansong_id');
        $store_id = $request->input('store_id');
        if (!$mansong_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $res=Voucher::delMansong(['mansong_id'=>$mansong_id]);
        if ($res) {
            return Base::jsonReturn(200, '删除成功');
        } else {
            return Base::jsonReturn(2000,  '删除失败');
        }
    }
    public function xianshiAdd(Request $request)
    {
        $store_id = $request->input('store_id');
        $xianshi_name = $request->input('xianshi_name');
        $xianshi_title = $request->input('xianshi_title');
        $xianshi_explain = $request->input('xianshi_explain');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $lower_limit= $request->input('lower_limit');
        if (!$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $xianshi_quota_list=Voucher::getXianShiInfo(['store_id'=>$store_id]);
        if(empty($xianshi_quota_list))
        {
            return Base::jsonReturn(2000,  '你还没有购买套餐');
        }
        $storeInfo=Store::getStoreInfo(['store_id'=>$store_id]);
        $data=array(
            'xianshi_name'=>$xianshi_name,
            'xianshi_title'=>$xianshi_title,
            'xianshi_explain'=>$xianshi_explain,
            'start_time'=>$start_time,
            'end_time'=>$end_time,
            'lower_limit'=>$lower_limit,
            'quota_id'=>$xianshi_quota_list->quota_id,
            'member_id'=>$storeInfo->member_id,
            'member_name'=>$storeInfo->member_name,
            'store_name'=>$storeInfo->store_name,
        );
        $xianshi_id=Voucher::addXianShiData($data);
        $array=array(
            array(
                'goods_id'=>100058,
                'xianshi_price'=>666
            )
        );
        foreach($array as $v)
        {
            $goods_info=Goods::getGoodsInfo(['goods_id'=>$goods_id]);
            $ins_data=array(
                'xianshi_id'=>$xianshi_id,
                'xianshi_name'=>$xianshi_name,
                'xianshi_title'=>$xianshi_title,
                'xianshi_explain'=>$xianshi_explain,
                'goods_id'=>$v['goods_id'],
                'store_id'=>$store_id,
                'goods_name'=>$goods_info->goods_name,
                'goods_price'=>$goods_info->goods_price,
                'goods_image'=>$goods_info->goods_image,
                'start_time'=>$start_time,
                'end_time'=>$end_time,
                'lower_limit'=>$lower_limit,
            );
            Voucher::addXianShiGoodsData($ins_data);
        }
        return Base::jsonReturn(200,  '添加成功');
    }
    public function xianshiList(Request $request)
    {
        $store_id = $request->input('store_id');
        if (!$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $list=Voucher::getXianshiList(['store_id'=>$store_id]);
        dd($list);
        if(!empty($list))
        {
            $result=array();
            foreach ($list as $k=>$v)
            {
                $result[$k]['xianshi_name']=$v->xianshi_name;
                $result[$k]['start_time']=$v->start_time;
                $result[$k]['end_time']=$v->end_time;
                $result[$k]['state']=$v->state;
            }
            return Base::jsonReturn(200,  '获取成功',$result);
        }else{
            return Base::jsonReturn(200,  '获取成功');
        }
    }
    public function xianshiDel(Request $request)
    {
        $xianshi_id = $request->input('xianshi_id');
        $store_id = $request->input('store_id');
        if (!$xianshi_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $res=Voucher::delXianshi(['xianshi_id'=>$xianshi_id]);
        if ($res) {
            return Base::jsonReturn(200, '删除成功');
        } else {
            return Base::jsonReturn(2000,  '删除失败');
        }
    }



}
