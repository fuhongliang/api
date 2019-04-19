<?php

namespace App\Http\Controllers\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController as Base;

use App\model\V2\Goods;
use App\model\V2\Store;
use App\model\V2\Voucher;
use Illuminate\Support\Facades\DB;


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
        $where=[
            ['quota_storeid', '=', $store_id],
            ['quota_endtime', '>', time()],
        ];
//        $quotainfo=Voucher::getVoucherQuotaInfo($where);
//        if(empty($quotainfo)){
//            return Base::jsonReturn(2000,  '你还没有购买代金券套餐');
//        }
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
        $insert_arr['voucher_t_creator_id'] = $quotainfo->quota_memberid;
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
        $info=Voucher::addVoucherInfo(['voucher_t_id'=>$voucher_id],$field);
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
        if (!$store_id || !$bundling_name || !$bl_discount_price || !$goods_list) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $data=array(
            'store_id'=>$store_id,
            'bl_name'=>$bundling_name,
            'store_name'=>Store::getStoreField(['store_id'=>$store_id],'store_name'),
            'bl_discount_price'=>$bl_discount_price,
            'bl_freight_choose'=>1,
            'bl_freight'=>0,

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
            $goods_info = Goods::getGoodsInfo(['goods_id'=>$val['goods_id']],['goods_id','goods_name','goods_image','store_id']);
            $array = array();
            $array['bl_id'] = $bundling_id;
            $array['goods_id'] = $val['goods_id'];
            $array['goods_name'] = empty($goods_info->goods_name) ? "":$goods_info->goods_name;
            $array['goods_image'] =empty( $goods_info->goods_image)? "": $goods_info->goods_image;
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
    public function bundlingInfo(Request $request)
    {
        $bundling_id = $request->input('bundling_id');
        $store_id = $request->input('store_id');
        if (!$bundling_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $res=Voucher::getBundlingInfo($bundling_id);
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
//        $rules=array(
//            array(
//                'price'=>100058,'discount'=>333,'mansong_goods_name'=>333
//            ),
//            array(
//                'price'=>100058,'discount'=>333,'mansong_goods_name'=>333
//            ),
//        );
//        $mansong_quota_list=Voucher::getManSongInfo(['store_id'=>$store_id],['quota_id']);
//        if(empty($mansong_quota_list))
//        {
//            return Base::jsonReturn(2000,  '你还没有购买套餐');
//        }
        $storeInfo=Store::getStoreInfo(['store_id'=>$store_id]);
        $data=array(
            'store_id'=>$store_id,
            'mansong_name'=>$mansong_name,
            'start_time'=>strtotime($start_time),
            'end_time'=>strtotime($end_time),
            'quota_id'=>1,
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
//        $xianshi_quota_list=Voucher::getXianShiInfo(['store_id'=>$store_id]);
//        if(empty($xianshi_quota_list))
//        {
//            return Base::jsonReturn(2000,  '你还没有购买套餐');
//        }
        $storeInfo=Store::getStoreInfo(['store_id'=>$store_id]);
        $data=array(
            'store_id'=>$store_id,
            'xianshi_name'=>$xianshi_name,
            'xianshi_title'=>$xianshi_title,
            'xianshi_explain'=>$xianshi_explain,
            'start_time'=>strtotime($start_time),
            'end_time'=>strtotime($end_time),
            'lower_limit'=>$lower_limit,
            'quota_id'=>1,
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
        $res=Voucher::delXianshi(['xianshi_id'=>$xianshi_id]);
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
        $res=Voucher::getXianshiInfoData($store_id,$xianshi_id);
        if ($res) {
            return Base::jsonReturn(200, '获取成功',$res);
        } else {
            return Base::jsonReturn(2000,  '获取失败');
        }
    }



}
