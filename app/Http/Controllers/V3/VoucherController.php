<?php

namespace App\Http\Controllers\V3;

use App\BModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController as Base;

use App\model\V3\Goods;
use App\model\V3\Store;
use App\model\V3\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class VoucherController extends Base
{
    /**  添加/编辑代金券
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function voucherEdit(Request $request){
        $store_id=$request->input('store_id');
        $voucher_id=$request->input('voucher_id');
        $voucher_t_title=$request->input('title');
        $voucher_t_price=$request->input('mianzhi');
        $limit=$request->input('limit_price');
        $describe=$request->input('describe');
        $enddate=strtotime($request->input('end_time'));
        $total=$request->input('total_nums');
        $eachlimit=$request->input('each_limit');
        if(!$store_id || !$voucher_t_title || !$voucher_t_price || !$limit || !$describe || !$enddate || !$total || !$eachlimit)
        {
            return Base::jsonReturn(1000,'参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000,  '商家不存在');
        }
        $where=[
            ['quota_storeid', '=', $store_id],
            ['quota_endtime', '>', time()],
        ];
        $quotainfo=Voucher::getVoucherQuotaInfo($where);
        if(empty($quotainfo)){
            return Base::jsonReturn(2000,  '你还没有购买代金券套餐');
        }
        $count=Voucher::getVoucherTemplateCount(['voucher_t_quotaid'=>1,'voucher_t_state'=>1]);
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
        $insert_arr['voucher_t_creator_id'] = $quotainfo->quota_storeid;
        $insert_arr['voucher_t_state'] = 1;
        $insert_arr['voucher_t_total'] = $total;
        $insert_arr['voucher_t_giveout'] = 0;
        $insert_arr['voucher_t_used'] = 0;
        $insert_arr['voucher_t_add_date'] = time();
        $insert_arr['voucher_t_quotaid'] = 1;
        $insert_arr['voucher_t_points'] = 0;
        $insert_arr['voucher_t_eachlimit'] = $eachlimit;
        if($voucher_id)
        {
            $res=Voucher::upVoucherTemplate(['voucher_t_id'=>$voucher_id],$insert_arr);
        }else{
            $res=Voucher::addVoucherTemplate($insert_arr);
        }
        if ($res) {
            return Base::jsonReturn(200, '操作成功');
        } else {
            return Base::jsonReturn(2000,  '操作失败');
        }
    }

    public function voucherInfo(Request $request){
        $voucher_id=$request->input('voucher_id');
        if(!$voucher_id)
        {
            return Base::jsonReturn(1000,'参数缺失');
        }
        $field=['voucher_t_id as voucher_id','voucher_t_title as voucher_title','voucher_t_price as voucher_price',
            'voucher_t_limit as voucher_limit','voucher_t_end_date as voucher_end_date',
            'voucher_t_total as voucher_total','voucher_t_eachlimit as voucher_eachlimit','voucher_t_desc as voucher_desc'];
        $info=Voucher::getVoucherInfo(['voucher_t_id'=>$voucher_id],$field);
        $info->voucher_end_date=date('Y-m-d H:i:s',$info->voucher_end_date);
        return Base::jsonReturn(200, '获取成功',$info);
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
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000,  '商家不存在');
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
    public function bundlingEdit(Request $request)
    {
        $store_id = $request->input('store_id');
        $bundling_name = $request->input('bundling_name');
        $bl_discount_price = $request->input('discount_price');
        $goods_list = $request->input('goods_list');
        $bundling_id = $request->input('bundling_id');
        $state = $request->input('bl_state');
        if (!$store_id || !$bundling_name || !$bl_discount_price || !$goods_list) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000,  '商家不存在');
        }
        $data=array(
            'store_id'=>$store_id,
            'bl_name'=>$bundling_name,
            'store_name'=>Store::getStoreField(['store_id'=>$store_id],'store_name'),
            'bl_discount_price'=>Base::ncPriceFormat($bl_discount_price),
            'bl_freight_choose'=>1,
            'bl_freight'=>0,
            'bl_state'=>$state == 1? 1:0
        );
        if($bundling_id)
        {
            Voucher::upBundlingData(['bl_id'=>$bundling_id],$data);
            DB::table('p_bundling_goods')
                ->where('bl_id',$bundling_id)
                ->delete();
        }else{
            $bundling_id=Voucher::addBundlingData($data);
        }
        foreach ($goods_list as $key => $val){
            $goods_info = Goods::getGoodsInfo(['a.goods_id'=>$val['goods_id']],['a.goods_id','a.goods_name','a.goods_image','a.store_id']);
            $array = array();
            $array['bl_id'] = $bundling_id;
            $array['goods_id'] = $val['goods_id'];
            $array['goods_name'] = $goods_info->goods_name;
            $array['goods_image'] =$goods_info->goods_image;
            $array['bl_goods_price'] = Base::ncPriceFormat($val['goods_price']);
            $array['bl_appoint'] = 1;
            Voucher::addBundlingGoodsData($array);
        }
        return Base::jsonReturn(200, '操作成功');

    }
    public function bundlingList(Request $request)
    {
        $store_id = $request->input('store_id');
        if(!$store_id)
        {
            return Base::jsonReturn(1000,'参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000,  '商家不存在');
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
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2001,  '商家不存在');
        }
        $res=Voucher::delBundling(['bl_id'=>$bundling_id]);
        if ($res) {
            return Base::jsonReturn(200, '删除成功');
        } else {
            return Base::jsonReturn(2000,  '删除失败');
        }
    }
    public function bundlingInfo(Request $request)
    {
        $bundling_id = $request->input('bundling_id');
        $store_id = $request->input('store_id');
        if (!$bundling_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2001,  '商家不存在');
        }
        $res=Voucher::getBundlingInfo($store_id,$bundling_id);
        if ($res) {
            return Base::jsonReturn(200, '获取成功',$res);
        } else {
            return Base::jsonReturn(2000,  '获取失败');
        }
    }

    public function mamsongEdit(Request $request)
    {
        $store_id = $request->input('store_id');
        $mansong_id = $request->input('mansong_id');
        $mansong_name = $request->input('mansong_name');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $remark = $request->input('remark');
        $rules = $request->input('rules');//{['price'=>100058,'discount'=>333,'mansong_goods_name'=>333],['price'=>100058,'discount'=>333,'mansong_goods_name'=>333]}
        if (!$store_id || !$mansong_name || !$start_time || !$end_time  ||!$rules) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000,  '商家不存在');
        }
        $mansong_quota_list=Voucher::getManSongInfo(['store_id'=>$store_id],['quota_id']);
        if(empty($mansong_quota_list))
        {
            return Base::jsonReturn(2000,  '你还没有购买套餐');
        }
        $storeInfo=Store::getStoreInfo(['store_id'=>$store_id]);
        $data=array(
            'store_id'=>$store_id,
            'mansong_name'=>$mansong_name,
            'start_time'=>strtotime($start_time),
            'end_time'=>strtotime($end_time),
            'quota_id'=>$mansong_quota_list->quota_id,
            'member_id'=>$storeInfo->member_id,
            'member_name'=>$storeInfo->member_name,
            'store_name'=>$storeInfo->store_name,
            'state'=>2,
            'remark'=>empty($remark) ? "":$remark
        );
        if($mansong_id)
        {
            Voucher::addManSongData($data);
            DB::table('p_mansong_rule')
                ->where('mansong_id',$mansong_id)
                ->delete();
        }else{
            $mansong_id=Voucher::addManSongData($data);
        }

        foreach($rules as $v)
        {
            $arr=array(
                'mansong_id'=>$mansong_id,
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
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000,  '商家不存在');
        }
        $list=Voucher::getManSongList(['store_id'=>$store_id]);
        if(!empty($list))
        {
            $result=array();
            foreach ($list as $k=>$v)
            {
                $result[$k]['mansong_id']=$v->mansong_id;
                $result[$k]['mansong_name']=$v->mansong_name;
                $result[$k]['start_time']=$v->start_time;
                $result[$k]['end_time']=$v->end_time;
                $result[$k]['state']=$v->state;
                if($v->end_time <= time())
                {
                    $result[$k]['state']=5;
                    Voucher::upManSongData(['mansong_id'=>$v->mansong_id],['state'=>5]);
                }
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
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000,  '商家不存在');
        }
        $res=Voucher::delMansong(['mansong_id'=>$mansong_id]);
        if ($res) {
            return Base::jsonReturn(200, '删除成功');
        } else {
            return Base::jsonReturn(2000,  '删除失败');
        }
    }
    public function xianshiEdit(Request $request)
    {
        $store_id = $request->input('store_id');
        $xianshi_id = $request->input('xianshi_id');
        $xianshi_name = $request->input('xianshi_name');
        $xianshi_title = $request->input('xianshi_title');
        $xianshi_explain = $request->input('xianshi_explain');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $lower_limit= $request->input('lower_limit');
        $goods_list= $request->input('goods_list');
        if (!$store_id || !$xianshi_name  || !$start_time ||!$end_time || !$lower_limit || !$goods_list) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000,  '商家不存在');
        }
        $xianshi_quota_list=Voucher::getXianShiInfo(['store_id'=>$store_id]);
        if(empty($xianshi_quota_list))
        {
            return Base::jsonReturn(2000,  '你还没有购买套餐');
        }
        $storeInfo=Store::getStoreInfo(['store_id'=>$store_id]);
        $data=array(
            'store_id'=>$store_id,
            'xianshi_name'=>$xianshi_name,
            'xianshi_title'=>$xianshi_title,
            'xianshi_explain'=>$xianshi_explain,
            'start_time'=>strtotime($start_time),
            'end_time'=>strtotime($end_time),
            'lower_limit'=>$lower_limit,
            'quota_id'=>$xianshi_quota_list->quota_id,
            'member_id'=>$storeInfo->member_id,
            'member_name'=>$storeInfo->member_name,
            'store_name'=>$storeInfo->store_name,
        );
        if($xianshi_id)
        {
            Voucher::upXianShiData(['xianshi_id'=>$xianshi_id],$data);
            DB::table('p_xianshi_goods')
                ->where('xianshi_id',$xianshi_id)
                ->delete();
        }else{
            $xianshi_id=Voucher::addXianShiData($data);

        }
        foreach($goods_list as $v)
        {
            $goods_info=Goods::getGoodsInfo(['goods_id'=>$v['goods_id']]);
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
                'start_time'=>strtotime($start_time),
                'end_time'=>strtotime($end_time),
                'lower_limit'=>$lower_limit,
                'xianshi_price'=>$v['xianshi_price']
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
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000,  '商家不存在');
        }
        $list=Voucher::getXianshiList(['store_id'=>$store_id]);
        if(!empty($list))
        {
            $result=array();
            foreach ($list as $k=>$v)
            {
                $result[$k]['xianshi_id']=$v->xianshi_id;
                $result[$k]['xianshi_name']=$v->xianshi_name;
                $result[$k]['start_time']=$v->start_time;
                $result[$k]['end_time']=$v->end_time;
                $result[$k]['state']=$v->state;
                if($v->end_time <= time())
                {
                    $result[$k]['state']=0;
                    Voucher::upXianShiData(['xianshi_id'=>$v->xianshi_id],['state'=>0]);
                }
                $result[$k]['lower_limit']=$v->lower_limit;
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
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000,  '商家不存在');
        }
        $res=Voucher::delXianshi(['xianshi_id'=>$xianshi_id,'store_id'=>$store_id]);
        if ($res) {
            return Base::jsonReturn(200, '删除成功');
        } else {
            return Base::jsonReturn(2000,  '删除失败');
        }
    }

    public function mianzhiList(Request $request)
    {
        $res=Voucher::getMianzhiList(['voucher_price_id','voucher_price']);
        if ($res) {
            return Base::jsonReturn(200, '获取成功',$res);
        } else {
            return Base::jsonReturn(2000,  '获取失败');
        }
    }
    public function xianshiInfo(Request $request)
    {
        $xianshi_id = $request->input('xianshi_id');
        $store_id = $request->input('store_id');
        if (!$xianshi_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000,  '商家不存在');
        }
        $res=Voucher::getXianshiInfoData($store_id,$xianshi_id);
        if ($res) {
            return Base::jsonReturn(200, '获取成功',$res);
        } else {
            return Base::jsonReturn(2000,  '获取失败');
        }
    }

    /**购买限时折扣套餐
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addXianshiQuoTa(Request $request)
    {
        $month = $request->input('month');
        $store_id = $request->input('store_id');
        if (!$month  || !$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if ($month <= 0 || $month > 12) {
            return Base::jsonReturn(2000,  '参数错误，购买失败.');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2001,  '商家不存在');
        }
        if(Voucher::checkQuoTaExist('p_xianshi_quota',['store_id'=>$store_id]))
        {
            return Base::jsonReturn(2002,  '已经过买过套餐');
        }
        $store_name=Store::getStoreField(['store_id'=>$store_id],'store_name');
        $add_time = 86400 *30 * $month;
        $now=time();
        $storeInfo= Store::getStoreInfo(['store_id'=>$store_id]);

        $param = array();
        $param['member_id'] = $storeInfo->member_id;
        $param['member_name'] = $storeInfo->member_name;
        $param['store_id'] = $store_id;
        $param['store_name'] = $store_name;
        $param['start_time'] = $now;
        $param['end_time'] = $now + $add_time;
        BModel::insertData('p_xianshi_quota',$param);
        $current_price = 20;
        Voucher::recordStoreCost($store_id,$current_price * $month, '购买限时折扣');
        Voucher::recordSellerLog($store_id,$store_name,'购买'.$month.'份限时折扣套餐，单价'.$current_price."元");
        return Base::jsonReturn(200,  '添加成功');
    }

    /**购买满送套餐
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addManSongQuoTa(Request $request)
    {
        $month = $request->input('month');
        $store_id = $request->input('store_id');
        if (!$month  || !$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if ($month <= 0 || $month > 12) {
            return Base::jsonReturn(2000,  '参数错误，购买失败.');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2001,  '商家不存在');
        }
        if(Voucher::checkQuoTaExist('p_mansong_quota',['store_id'=>$store_id]))
        {
            return Base::jsonReturn(2002,  '已经过买过套餐');
        }
        $store_name=Store::getStoreField(['store_id'=>$store_id],'store_name');
        $add_time = 86400 *30 * $month;
        $now=time();
        $storeInfo= Store::getStoreInfo(['store_id'=>$store_id]);

        $param = array();
        $param['member_id'] = $storeInfo->member_id;
        $param['apply_id'] = 0;
        $param['member_name'] = $storeInfo->member_name;
        $param['store_id'] = $store_id;
        $param['store_name'] = $store_name;
        $param['start_time'] = $now;
        $param['end_time'] = $now + $add_time;
        $param['state'] = 0;
        BModel::insertData('p_mansong_quota',$param);
        $current_price = 20;
        Voucher::recordStoreCost($store_id,$current_price * $month, '购买满即送');
        Voucher::recordSellerLog($store_id,$store_name,'购买'.$month.'份满即送套餐，单价'.$current_price."元");
        return Base::jsonReturn(200,  '添加成功');
    }

    /**购买优惠套餐
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBundlingQuoTa(Request $request)
    {
        $month = $request->input('month');
        $store_id = $request->input('store_id');
        if (!$month  || !$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if ($month <= 0 || $month > 12) {
            return Base::jsonReturn(2000,  '参数错误，购买失败.');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2001,  '商家不存在');
        }
        if(Voucher::checkQuoTaExist('p_bundling_quota',['store_id'=>$store_id]))
        {
            return Base::jsonReturn(2002,  '已经过买过套餐');
        }
        $store_name=Store::getStoreField(['store_id'=>$store_id],'store_name');
        $now=time();
        $storeInfo= Store::getStoreInfo(['store_id'=>$store_id]);

        $data = array();
        $data['member_id'] = $storeInfo->member_id;
        $data['member_name'] = $storeInfo->member_name;
        $data['store_id'] = $store_id;
        $data['store_name'] = $store_name;
        $data['bl_quota_month']     = $month;
        $data['bl_quota_starttime'] = $now;
        $data['bl_quota_endtime']   = $now + 60 * 60 * 24 * 30 * $month;
        $data['bl_state']     = 1;

        BModel::insertData('p_bundling_quota',$data);
        $current_price = 20;
        Voucher::recordStoreCost($store_id,$current_price * $month, '购买优惠套装');
        $end_time = $now + 60 * 60 * 24 * 30 * $month;
        Voucher::addcron(array('exetime' => $end_time, 'exeid' =>$store_id, 'type' => 3), true);
        Voucher::recordSellerLog($store_id,$store_name,'购买'.$month.'套优惠套装，单价'.$current_price."元");
        return Base::jsonReturn(200,  '添加成功');
    }

    /**购买代金券
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addVoucherQuoTa(Request $request)
    {
        $month = $request->input('month');
        $store_id = $request->input('store_id');
        if (!$month  || !$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if ($month <= 0 || $month > 12) {
            return Base::jsonReturn(2000,  '参数错误，购买失败.');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2001,  '商家不存在');
        }
        if(Voucher::checkQuoTaExist('voucher_quota',['store_id'=>$store_id]))
        {
            return Base::jsonReturn(2002,  '已经过买过套餐');
        }
        $store_name=Store::getStoreField(['store_id'=>$store_id],'store_name');
        $add_time = 86400 *30 * $month;
        $now=time();
        $storeInfo= Store::getStoreInfo(['store_id'=>$store_id]);
        $param = array();
        $param['quota_memberid'] = $storeInfo->member_id;
        $param['quota_membername'] = $storeInfo->member_name;
        $param['quota_storeid'] = $store_id;
        $param['quota_storename'] = $store_name;
        $param['quota_starttime'] = $now;
        $param['quota_endtime'] = $now + $add_time;
        $param['quota_state'] = 1;
        BModel::insertData('voucher_quota',$param);
        $current_price = 20;
        Voucher::recordStoreCost($store_id,$current_price * $month, '购买代金券套餐');
        Voucher::recordSellerLog($store_id,$store_name,'购买'.$month.'份代金券套餐，单价'.$current_price."元");
        return Base::jsonReturn(200,  '添加成功');
    }

}
