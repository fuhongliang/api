<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\BaseController as Base;
use App\Http\Controllers\SMSController;
use App\Http\Controllers\BaseController;
use App\model\V2\Goods;
use App\model\V2\Member;
use App\model\V2\Order;
use App\model\V2\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Overtrue\EasySms\EasySms;

class StoreController extends Base
{
    public function addStoreGoodsClass(Request $request)
    {
        $class_id = $request->input('class_id');
        $store_id = $request->input('store_id');
        $class_name = $request->input('class_name');
        if (empty($store_id) || empty($class_name)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if(!$class_id)
        {
            $is_exist = Store::checkStoreGoodsClassExist($store_id, $class_name);
            if ($is_exist) {
                $ins_data = array(
                    'stc_name' => $class_name,
                    'store_id' => $store_id,
                    'stc_parent_id' => 0,
                    'stc_state' => 1,
                    'stc_sort' => 0
                );
                $res = Store::addStoreGoodsClass($ins_data);
                if ($res) {
                    $data=Store::getAllStoreClass(['store_id'=>$store_id],['stc_id','stc_name','stc_sort']);
                    return Base::jsonReturn(200, '新增成功',$data);
                } else {
                    return Base::jsonReturn(2000, '新增失败');
                }
            } else {
                return Base::jsonReturn(2000, '名称已存在');
            }
        }else{
            //存在检测重名
            $store_info=Store::getStoreClassInfo(['store_id'=>$store_id,'stc_name'=>$class_name]);
            if (empty($store_info))
            {
                $res = Store::editStoreClassInfo(['stc_id'=>$class_id],['stc_name'=>$class_name]);
                if ($res) {
                   $data=Store::getAllStoreClass(['store_id'=>$store_id],['stc_id','stc_name','stc_sort']);
                    return Base::jsonReturn(200, '更新成功',$data);
                } else {
                    return Base::jsonReturn(2000, null, '更新失败');
                }
            }elseif ( $class_id == $store_info->stc_id)
            {
                $data=Store::getAllStoreClass(['store_id'=>$store_id],['stc_id','stc_name','stc_sort']);

               return Base::jsonReturn(200, '更新成功',$data);
            }else{
                return Base::jsonReturn(2000, '名称已存在');
            }
        }

    }
    public function delStoreGoodsClass(Request $request)
    {
        $class_id = $request->input('class_id');
        $store_id = $request->input('store_id');
        if (empty($class_id) || empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $res=Store::delStoreClassInfo($class_id,$store_id);
        if ($res) {
            return Base::jsonReturn(200, '删除成功');
        } else {
            return Base::jsonReturn(2000, '删除失败');
        }
    }
    public function storeGoodsClassList(Request $request)
    {
        $store_id = $request->input('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $data=Store::getAllStoreClass(['store_id'=>$store_id],['stc_id','stc_name','stc_sort']);
        return Base::jsonReturn(200, '获取成功',$data);
    }
    public function sortStoreGoodsClass(Request $request)
    {
        $class_ids = json_decode($request->input('class_ids'));
        $store_id = $request->input('store_id');
        if (empty($class_ids) || empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $res=Store::sortStoreGoodsClass($class_ids,$store_id);
        if ($res) {
            $data=Store::getAllStoreClass(['store_id'=>$store_id],['stc_id','stc_name','stc_sort']);
            return Base::jsonReturn(200,  '排序成功',$data);
        } else {
            return Base::jsonReturn(2000,  '排序失败');
        }
    }
    public function storeGoodsList(Request $request)
    {
        $class_id = $request->input('class_id');
        $store_id = $request->input('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000,  '参数缺失');
        }
        if(empty($class_id)) {
            $stcId = Store::getStoreClassStcId(['store_id' => $store_id], ['stc_id']);
            if(!$stcId)
            {
                $result['class_list']=null;
                $result['goods_list']=null;
                return Base::jsonReturn(200,  '获取成功',$result);
            }
            $class_id=$stcId->stc_id;
        }
        $result=array();
        $result['class_list']=Store::getAllStoreClass(['store_id'=>$store_id],['stc_id','stc_name']);
        $result['goods_list']=Store::getStoreGoodsListByStcId($store_id,$class_id);
        return Base::jsonReturn(200,  '获取成功',$result);

    }
    public function getStoreSetting(Request $request)
    {
        $store_id = $request->input('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $data=Store::getStoreData(['a.store_id'=>$store_id],
            ['a.store_state','a.store_description','a.store_label','a.store_phone',
                'a.area_info','a.store_address','a.store_workingtime','b.business_licence_number_electronic']);
        $data['store_zizhi']=config('data_host').'upload/shop/store_joinin/'.$data['store_zizhi'];
        $field= ['a.store_id','a.store_name','a.store_avatar','a.work_start_time','a.work_end_time','c.member_id','c.member_mobile'];
        $result=Store::getStoreAndJoinInfo(['a.store_id'=>$store_id],$field);

        $data['store_id']=$result->store_id;
        $data['store_name']=$result->store_name;
        $data['store_avatar']=$result->store_avatar;
        $data['work_start_time']=$result->work_start_time;
        $data['work_end_time']=$result->work_end_time;
        $data['member_id']=$result->member_id;
        $data['member_mobile']=$result->member_mobile;
        return Base::jsonReturn(200, '获取成功',$data);
    }
    public function setWorkState(Request $request)
    {
        $store_id = $request->input('store_id');
        $store_state = $request->input('store_state');
        if (empty($store_id)) {
            return Base::jsonReturn(1000,  '参数缺失');
        }
        $res=Store::setWorkState(['store_id'=>$store_id],['store_state'=>$store_state]);
        if ($res) {
            return Base::jsonReturn(200,  '设置成功');
        } else {
            return Base::jsonReturn(2000, '设置失败');
        }
    }
    public function setStoreDesc(Request $request)
    {
        $store_id = $request->input('store_id');
        $store_desc = $request->input('store_desc');
        if (empty($store_id) || empty($store_desc)) {
            return Base::jsonReturn(1000,  '参数缺失');
        }
        $res=Store::setWorkState(['store_id'=>$store_id],['store_description'=>$store_desc]);
        if ($res) {
            return Base::jsonReturn(200,  '设置成功');
        } else {
            return Base::jsonReturn(2000,  '设置失败');
        }
    }
    public function setStorePhone(Request $request)
    {
        $store_id = $request->input('store_id');
        $phone_number = $request->input('phone_number');
        if (empty($store_id) || empty($phone_number)) {
            return Base::jsonReturn(1000,  '参数缺失');
        }
        $res=Store::setWorkState(['store_id'=>$store_id],['store_phone'=>$phone_number]);
        if ($res) {
            return Base::jsonReturn(200,  '设置成功');
        } else {
            return Base::jsonReturn(2000,  '设置失败');
        }
    }
    public function setStoreWorkTime(Request $request)
    {
        $store_id = $request->input('store_id');
        $work_start_time = $request->input('work_start_time');
        $work_end_time = $request->input('work_end_time');
        if (empty($store_id) || empty($work_start_time) || empty($work_end_time)) {
            return Base::jsonReturn(1000,  '参数缺失');
        }
        $res=Store::setWorkState(['store_id'=>$store_id],['work_start_time'=>$work_start_time,'work_end_time'=>$work_end_time]);
        if ($res) {
            return Base::jsonReturn(200,  '设置成功');
        } else {
            return Base::jsonReturn(2000,  '设置失败');
        }

    }
    public function msgFeedBack(Request $request)
    {
        $store_id = $request->input('store_id');
        $content = $request->input('content');
        $type = $request->input('type');// 1 安卓 2 ios
        if (empty($store_id) || empty($content) || empty($type)) {
            return Base::jsonReturn(1000,  '参数缺失');
        }
        $memdata=Store::getStoreMemInfo(['store_id'=>$store_id],['c.member_id','c.member_name']);
        $data=array(
            'member_id'=>$memdata->member_id,
            'member_name'=>$memdata->member_name,
            'store_id'=>$store_id,
            'content'=>$content,
            'ftime'=>time(),
            'type'=>$type == 1? 3:4
        );
        $res=Store::addAppFeedBack($data);
        if ($res) {
            return Base::jsonReturn(200,  '反馈成功');
        } else {
            return Base::jsonReturn(2000,  '反馈失败');
        }

    }
    public function editPasswd(Request $request)
    {
        $member_id = $request->input('member_id');
        $phone_number = $request->input('phone_number');
        $verify_code = $request->input('verify_code');
        $new_passwd = $request->input('new_passwd');
        $con_new_passwd = $request->input('con_new_passwd');
        if (empty($member_id) || empty($verify_code) || empty($new_passwd) || empty($con_new_passwd)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if($new_passwd !==$con_new_passwd)
        {
            return Base::jsonReturn(2001, '密码不一致');
        }
        if(strlen(trim($new_passwd))<6)
        {
            return Base::jsonReturn(2002,  '密码最少6位');
        }
        $code=Cache::get($phone_number);
        if(!$code || $code !== $verify_code)
        {
            return Base::jsonReturn(2003, '验证码错误');
        }else{
            $res=Member::editMemberInfo(['member_id'=>$member_id],['member_passwd'=>md5($new_passwd)]);
            if ($res) {
                return Base::jsonReturn(200,  '修改成功');
            } else {
                return Base::jsonReturn(2000,  '修改失败');
            }
        }
    }

    public function getStoreCom(Request $request)
    {
        $store_id = $request->input('store_id');
        $haoping = $request->input('haoping');// 1 好评  2 中评 3 差评
        $no_com = $request->input('no_com');
        if (empty($store_id)) {
            return Base::jsonReturn(1000 ,'参数缺失');
        }
        $result=array();
        if(!empty($no_com))//未回复
        {
            $condition=['store_id'=>$store_id,'parent_id'=>0,'is_replay'=>0];
        }else{//全部
            $result['haoping']=Store::getComNums($store_id);
            if(!$haoping)
            {
                $condition=['store_id'=>$store_id,'parent_id'=>0];
            }else{
                $condition=['store_id'=>$store_id,'haoping'=>$haoping,'parent_id'=>0];
            }
        }
        $result['com_list']=Store::getStoreComAllData($condition);
        return Base::jsonReturn(200,  '获取成功',$result);
    }


    public function storeFeedback(Request $request)
    {
        $store_id = $request->input('store_id');
        $content = $request->input('content');
        $parent_id = $request->input('parent_id');
        if (empty($store_id) || empty($content)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $ins_data=array(
            'store_id'=>$store_id,
            'content'=>$content,
            'parent_id'=>$parent_id,
            'add_time'=>time()
        );
        DB::transaction(function ()  use ($ins_data,$parent_id){
            Store::addStoreCom($ins_data);
            Store::upStoreCom(['com_id' => $parent_id], ['is_replay'=>1]);
        });
        return Base::jsonReturn(200,  '回复成功');
    }

    public function storeYunYingInfo(Request $request)
    {
        $store_id = $request->input('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        //统计的日期0点
        $stat_time = strtotime(date('Y-m-d',time())) - 86400;
        /*
         * 近30天
         */
        $stime = $stat_time - (86400*29);//30天前
        $etime = $stat_time + 86400 - 1;//昨天23:59


        // 30天 下单量和销售金额

        $data=DB::table('order')
            ->where('store_id',$store_id)
            ->whereBetween('add_time',[$stime,$etime])
            ->first(
            array(
                DB::raw('COUNT(*) as ordernum'),
                DB::raw('IFNULL(SUM(order_amount),0) as orderamount')
            )
        );

        //店铺收藏量 商品数量
        $store_collect_data= Store::getStoreInfo(['store_id'=>$store_id],['store_collect']);
        $goods_num=Goods::getGoodsCount(['store_id'=>$store_id]);
        $data2 =DB::table('order')
            ->where('store_id',$store_id)
            ->whereBetween('add_time',[$beginToday,$endToday])
            ->first(
        array(
            DB::raw('COUNT(*) as ordernum'),
            DB::raw('IFNULL(SUM(order_amount),0) as orderamount')
            )
        );
        $result=array();
        $result['today_ordernum']=$data2->ordernum;
        $result['today_orderamount']=$data2->orderamount;
        $result['30_ordernum']=$data->ordernum;
        $result['30_orderamount']=$data->orderamount;
        $result['store_collect']=$store_collect_data->store_collect;
        $result['goods_num']=$goods_num;
        $result['jingying_url']='http://47.111.27.189:2000/v2/store_jingying/'.$store_id;
        return Base::jsonReturn(200, '获取成功', $result);
    }
    public function storeJingYingData(Request $request)
    {
        $flowstat_tablenum=3;
        $store_id = $request->route('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $data=array();
        $data['datetime']=date('Y-m-d');
        //确定统计分表名称
        $last_num = $store_id % 10; //获取店铺ID的末位数字
        $tablenum = ($t = intval($flowstat_tablenum)) > 1 ? $t : 1; //处理流量统计记录表数量
        $flow_tablename = ($t = ($last_num % $tablenum)) > 0 ? "flowstat_$t" : 'flowstat';
        //今日开始和结束时间
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

        //昨日开始和结束时间
        $beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        $endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;


        //点击量
        $today_click =DB::table($flow_tablename)
            ->where('store_id',$store_id)
            ->whereBetween('stattime',[$beginToday,$endToday])
            ->first(
            array(
                DB::raw('IFNULL(SUM(clicknum),0) as clicknum')
            )
        );
        $yest_click =DB::table($flow_tablename)
            ->where('store_id',$store_id)
            ->whereBetween('stattime',[$beginYesterday,$endYesterday])
            ->first(
            array(
                DB::raw('IFNULL(SUM(clicknum),0) as clicknum')
            )
        );
        $field=['COUNT(*) as ordernum'];

        //订单
        $today_ordernum=DB::table('order')
            ->where('store_id',$store_id)
            ->whereBetween('add_time',[$beginToday,$endToday])
            ->first(
            array(
                DB::raw('COUNT(*) as ordernum'),
                DB::raw('IFNULL(SUM(order_amount),0) as orderamount')
            )
        );
        $yest_ordernum=DB::table('order')
            ->where('store_id',$store_id)
            ->whereBetween('add_time',[$beginYesterday,$endYesterday])
            ->first(
            array(
                DB::raw('COUNT(*) as ordernum'),
                DB::raw('IFNULL(SUM(order_amount),0) as orderamount')
            )
        );

        if ($today_click->clicknum == 0)
        {
            $today_change=0;
        }else{
            $today_change=$today_ordernum->ordernum/$today_click->clicknum;
        }
        if ($yest_click->clicknum == 0)
        {
            $yest_change=0;
        }else{
            $yest_change=$yest_ordernum->ordernum/$yest_click->clicknum;
        }
        $data['store_id']=$store_id;
        $data['today_click']=$today_click->clicknum;
        $data['today_click_comp']=$today_click->clicknum-$yest_click->clicknum;
        $data['today_ordernum']=$today_ordernum->ordernum;
        $data['today_ordernum_comp']=$today_ordernum->ordernum-$yest_ordernum->ordernum;
        $data['today_change']=$today_change;
        $data['today_change_comp']=$today_change-$yest_change;
        return view('store.jingying', ['data'=>$data]);
    }
    public function getEcharts(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
        $store_id =$request->input('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000,'参数缺失');
        }
        $data=$xday=$ydata=$result=array();
        for ($i=7;$i>0;$i--)
        {
            $data[$i]['start_time']=mktime(0,0,0,date('m'),date('d'),date('Y'))-$i*3600*24 ;
            $data[$i]['end_time']=$data[$i]['start_time']+24*3600;
            array_push($xday,date('Y-m-d',$data[$i]['start_time']));
        }

        $field=['COUNT(*) as ordernum'];
        foreach ($data as $v)
        {
            $data=DB::table('order')
                ->where('store_id',$store_id)
                ->whereBetween('add_time',[$v['start_time'],$v['end_time']])
                ->first(
                array(
                    DB::raw('COUNT(*) as ordernum'),
                )
            );
            array_push($ydata,$data->ordernum);
        }
        $result['xday']=$xday;
        $result['ydata']=$ydata;
        return $result;

    }
    public function getEcharts_(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
        $store_id = $request->input('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $data=$xday=$ydata=$result=array();
        for ($i=7;$i>0;$i--)
        {
            $data[$i]['start_time']=mktime(0,0,0,date('m'),date('d'),date('Y'))-$i*3600*24 ;
            $data[$i]['end_time']=$data[$i]['start_time']+24*3600;
            array_push($xday,date('Y-m-d',$data[$i]['start_time']));
        }

        foreach ($data as $v)
        {
            $data=DB::table('order')
                ->where('store_id',$store_id)
                ->whereBetween('add_time',[$v['start_time'],$v['end_time']])
                ->first(
                array(
                    DB::raw('IFNULL(SUM(order_amount),0) as orderamount'),
                )
            );
            array_push($ydata,$data->orderamount);
        }
        $result['xday']=$xday;
        $result['ydata']=$ydata;
        return $result;
    }
    public function getSMS(Request $request)
    {
        $phone_number = $request->input('phone_number');
        if (empty($phone_number)) {
            return Base::jsonReturn(1000,  '参数缺失');
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$phone_number))
        {
            return Base::jsonReturn(1000, '手机号格式不正确');
        }
        $code=rand('1000','9999');
        $res=SMSController::sendSms($phone_number,$code);

        if ($res->Code == 'OK') {
            Cache::put($phone_number,$code,300);
            return Base::jsonReturn(200,  '发送成功');
        } else {
            return Base::jsonReturn(2000,  '发送失败');
        }

    }
}
