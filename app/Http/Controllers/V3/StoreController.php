<?php

namespace App\Http\Controllers\V3;

use App\BModel;
use App\Http\Controllers\BaseController as Base;
use App\Http\Controllers\SMSController;
use App\model\V3\Goods;
use App\model\V3\Member;
use App\model\V3\Store;
use App\model\V3\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Overtrue\EasySms\EasySms;

class StoreController extends Base
{
    public function addStoreGoodsClass(Request $request)
    {
        $class_id   = $request->input('class_id');
        $store_id   = $request->input('store_id');
        $class_name = $request->input('class_name');
        if (empty($store_id) || empty($class_name)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        if (!$class_id) {
            $is_exist = Store::checkStoreGoodsClassExist($store_id, $class_name);
            if ($is_exist) {
                $ins_data = array(
                    'stc_name' => $class_name,
                    'store_id' => $store_id,
                    'stc_parent_id' => 0,
                    'stc_state' => 1,
                    'stc_sort' => 0
                );
                $res      = Store::addStoreGoodsClass($ins_data);
                if ($res) {
                    $data = Store::getAllStoreClass(['store_id' => $store_id], ['stc_id', 'stc_name', 'stc_sort']);
                    return Base::jsonReturn(200, '新增成功', $data);
                } else {
                    return Base::jsonReturn(2000, '新增失败');
                }
            } else {
                return Base::jsonReturn(2000, '名称已存在');
            }
        } else {
            //存在检测重名
            $store_info = Store::getStoreClassInfo(['store_id' => $store_id, 'stc_name' => $class_name]);
            if (empty($store_info)) {
                $res = Store::editStoreClassInfo(['stc_id' => $class_id], ['stc_name' => $class_name]);
                if ($res) {
                    $data = Store::getAllStoreClass(['store_id' => $store_id], ['stc_id', 'stc_name', 'stc_sort']);
                    return Base::jsonReturn(200, '更新成功', $data);
                } else {
                    return Base::jsonReturn(2000, null, '更新失败');
                }
            } elseif ($class_id == $store_info->stc_id) {
                $data = Store::getAllStoreClass(['store_id' => $store_id], ['stc_id', 'stc_name', 'stc_sort']);

                return Base::jsonReturn(200, '更新成功', $data);
            } else {
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
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $res = Store::delStoreClassInfo($class_id, $store_id);
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
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $data = Store::getAllStoreClass(['store_id' => $store_id], ['stc_id', 'stc_name', 'stc_sort']);
        return Base::jsonReturn(200, '获取成功', $data);
    }

    public function sortStoreGoodsClass(Request $request)
    {
        $class_ids = json_decode($request->input('class_ids'));
        $store_id  = $request->input('store_id');
        if (empty($class_ids) || empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $res = Store::sortStoreGoodsClass($class_ids, $store_id);
        if ($res) {
            $data = Store::getAllStoreClass(['store_id' => $store_id], ['stc_id', 'stc_name', 'stc_sort']);
            return Base::jsonReturn(200, '排序成功', $data);
        } else {
            return Base::jsonReturn(2000, '排序失败');
        }
    }

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
        if (empty($class_id)) {
            $stcId = Store::getStoreClassStcId(['store_id' => $store_id], ['stc_id']);
            if (!$stcId) {
                $result['class_list'] = null;
                $result['goods_list'] = null;
                return Base::jsonReturn(200, '获取成功', $result);
            }
            $class_id = $stcId->stc_id;
        }
        $result               = array();
        $result['class_list'] = Store::getAllStoreClass(['store_id' => $store_id], ['stc_id', 'stc_name']);
        $result['goods_list'] = Store::getStoreGoodsListByStcId($store_id, $class_id);
        return Base::jsonReturn(200, '获取成功', $result);

    }

    public function getStoreSetting(Request $request)
    {
        $store_id = $request->input('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $data                = Store::getStoreData(['a.store_id' => $store_id],
            ['a.store_state', 'a.store_description', 'a.store_label', 'a.store_phone',
                'a.area_info', 'a.store_address', 'a.store_workingtime', 'b.business_licence_number_electronic']);
        $data['store_zizhi'] = config('data_host') . 'upload/shop/store_joinin/' . $data['store_zizhi'];

        $field  = ['a.store_id', 'a.store_name', 'a.store_avatar', 'a.work_start_time', 'a.auto_receive_order', 'a.work_end_time', 'c.member_id', 'c.member_mobile'];
        $result = Store::getStoreAndJoinInfo(['a.store_id' => $store_id], $field);

        $data['store_id']           = $result->store_id;
        $data['store_name']         = $result->store_name;
        $data['store_avatar']       = $result->store_avatar;
        $data['work_start_time']    = $result->work_start_time;
        $data['work_end_time']      = $result->work_end_time;
        $data['member_id']          = $result->member_id;
        $data['member_mobile']      = $result->member_mobile;
        $data['auto_receive_order'] = $result->auto_receive_order;
        return Base::jsonReturn(200, '获取成功', $data);
    }

    public function setWorkState(Request $request)
    {
        $store_id    = $request->input('store_id');
        $store_state = $request->input('store_state');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $res = Store::setWorkState(['store_id' => $store_id], ['store_state' => $store_state]);
        if ($res) {
            return Base::jsonReturn(200, '设置成功');
        } else {
            return Base::jsonReturn(2000, '设置失败');
        }
    }

    public function setStoreDesc(Request $request)
    {
        $store_id   = $request->input('store_id');
        $store_desc = $request->input('store_desc');
        if (empty($store_id) || empty($store_desc)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $res = Store::setWorkState(['store_id' => $store_id], ['store_description' => $store_desc]);
        if ($res) {
            return Base::jsonReturn(200, '设置成功');
        } else {
            return Base::jsonReturn(2000, '设置失败');
        }
    }

    public function setStorePhone(Request $request)
    {
        $store_id     = $request->input('store_id');
        $phone_number = $request->input('phone_number');
        if (empty($store_id) || empty($phone_number)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        if (!preg_match("/^(0[0-9]{2,3}-)?([2-9][0-9]{6,7})+(-[0-9]{1,4})?$/", $phone_number) && !preg_match("/^1[34578]{1}\d{9}$/", $phone_number)) {
            return Base::jsonReturn(2001, '电话格式不正确');
        }
        $res = Store::setWorkState(['store_id' => $store_id], ['store_phone' => $phone_number]);
        if ($res) {
            return Base::jsonReturn(200, '设置成功');
        } else {
            return Base::jsonReturn(2000, '设置失败');
        }
    }

    public function setStoreWorkTime(Request $request)
    {
        $store_id        = $request->input('store_id');
        $work_start_time = $request->input('work_start_time');
        $work_end_time   = $request->input('work_end_time');
        if (empty($store_id) || empty($work_start_time) || empty($work_end_time)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2001, '商家不存在');
        }
        $end_time   = str_replace(':', '', $work_end_time);
        $start_time = str_replace(':', '', $work_start_time);
        if (intval($start_time) > intval($end_time)) {
            return Base::jsonReturn(2002, '开始时间应小于结束时间');
        }
        $res = Store::setWorkState(['store_id' => $store_id], ['work_start_time' => $work_start_time, 'work_end_time' => $work_end_time]);
        if ($res) {
            return Base::jsonReturn(200, '设置成功');
        } else {
            return Base::jsonReturn(2000, '设置失败');
        }

    }

    public function msgFeedBack(Request $request)
    {
        $store_id = $request->input('store_id');
        $content  = $request->input('content');
        $type     = $request->input('type');// 1 安卓 2 ios
        if (empty($store_id) || empty($content) || empty($type)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $memdata = Store::getStoreMemInfo(['store_id' => $store_id], ['c.member_id', 'c.member_name']);
        $data    = array(
            'member_id' => $memdata->member_id,
            'member_name' => $memdata->member_name,
            'store_id' => $store_id,
            'content' => $content,
            'ftime' => time(),
            'type' => $type == 1 ? 3 : 4
        );
        $res     = Store::addAppFeedBack($data);
        if ($res) {
            return Base::jsonReturn(200, '反馈成功');
        } else {
            return Base::jsonReturn(2000, '反馈失败');
        }

    }

    public function editPasswd(Request $request)
    {
        $member_id      = $request->input('member_id');
        $phone_number   = $request->input('phone_number');
        $verify_code    = $request->input('verify_code');
        $new_passwd     = $request->input('new_passwd');
        $con_new_passwd = $request->input('con_new_passwd');
        if (empty($member_id) || empty($verify_code) || empty($new_passwd) || empty($con_new_passwd)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if ($new_passwd !== $con_new_passwd) {
            return Base::jsonReturn(2001, '密码不一致');
        }
        if (strlen(trim($new_passwd)) < 6) {
            return Base::jsonReturn(2002, '密码最少6位');
        }
        $code = Cache::get($phone_number);
        if (!$code || $code !== $verify_code) {
            return Base::jsonReturn(2003, '验证码错误');
        } else {
            $res = Member::editMemberInfo(['member_id' => $member_id], ['member_passwd' => md5($new_passwd)]);
            if ($res) {
                return Base::jsonReturn(200, '修改成功');
            } else {
                return Base::jsonReturn(2000, '修改失败');
            }
        }
    }

    public function getStoreCom(Request $request)
    {
        $store_id = $request->input('store_id');
        $haoping  = $request->input('haoping');// 1 好评  2 中评 3 差评
        $no_com   = $request->input('no_com');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $result = array();
        if (!empty($no_com))//未回复
        {
            $condition = ['store_id' => $store_id, 'parent_id' => 0, 'is_replay' => 0];
        } else {//全部
            $result['haoping'] = Store::getComNums($store_id);
            if (!$haoping) {
                $condition = ['store_id' => $store_id, 'parent_id' => 0];
            } else {
                $condition = ['store_id' => $store_id, 'haoping' => $haoping, 'parent_id' => 0];
            }
        }
        $result['com_list'] = Store::getStoreComAllData($condition);
        return Base::jsonReturn(200, '获取成功', $result);
    }


    public function storeFeedback(Request $request)
    {
        $store_id  = $request->input('store_id');
        $content   = $request->input('content');
        $parent_id = $request->input('parent_id');
        if (empty($store_id) || empty($content)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $ins_data = array(
            'store_id' => $store_id,
            'content' => $content,
            'parent_id' => $parent_id,
            'add_time' => time()
        );
        DB::transaction(function () use ($ins_data, $parent_id) {
            Store::addStoreCom($ins_data);
            Store::upStoreCom(['com_id' => $parent_id], ['is_replay' => 1]);
        });
        return Base::jsonReturn(200, '回复成功');
    }

    public function storeYunYingInfo(Request $request)
    {
        $store_id = $request->input('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday   = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        //统计的日期0点
        $stat_time = strtotime(date('Y-m-d', time())) - 86400;
        /*
         * 近30天
         */
        $stime = $stat_time - (86400 * 29);//30天前
        $etime = $stat_time + 86400 - 1;//昨天23:59


        // 30天 下单量和销售金额

        $data_30      = DB::table('order as a')
            ->leftJoin('order_goods as b', 'a.order_id', 'b.order_id')
            ->where('a.store_id', $store_id)
            ->whereBetween('a.add_time', [$stime, $etime])
            ->where('a.order_state', 40)
            ->get(['a.order_id', 'b.goods_pay_price', 'b.commis_rate']);
        $money_30     = 0;
        $order_ids_30 = [];
        if (!$data_30->isEmpty()) {
            foreach ($data_30 as $k => $v) {
                $money_30 += Base::ncPriceFormat(Base::ncPriceFormat($v->goods_pay_price) * (1 - intval($v->commis_rate) / 100));
                array_push($order_ids_30, $v->order_id);
            }
        }

        //店铺收藏量 商品数量
        $store_collect_data = BModel::getTableValue('store', ['store_id' => $store_id], 'store_collect');
        $goods_num          = BModel::getCount('goods', ['store_id' => $store_id]);

        $today_data  = DB::table('order as a')
            ->leftJoin('order_goods as b', 'a.order_id', 'b.order_id')
            ->where('a.store_id', $store_id)
            ->whereBetween('a.add_time', [$beginToday, $endToday])
            ->where('a.order_state', 40)
            ->get(['a.order_id', 'b.goods_pay_price', 'b.commis_rate']);
        $today_money = 0;
        $order_ids   = [];
        if (!$today_data->isEmpty()) {
            foreach ($today_data as $k => $v) {
                $today_money += Base::ncPriceFormat(Base::ncPriceFormat($v->goods_pay_price) * (1 - intval($v->commis_rate) / 100));
                array_push($order_ids, $v->order_id);
            }
        }
        $result                      = array();
        $result['today_ordernum']    = count(array_unique($order_ids));
        $result['today_orderamount'] = $today_money;
        $result['30_ordernum']       = count(array_unique($order_ids_30));
        $result['30_orderamount']    = $money_30;
        $result['store_collect']     = $store_collect_data;
        $result['goods_num']         = $goods_num;
        $result['jingying_url']      = 'http://47.111.27.189:2000/v3/store_jingying/' . $store_id;
        return Base::jsonReturn(200, '获取成功', $result);
    }

    public function storeJingYingData(Request $request)
    {
        $flowstat_tablenum = 3;
        $store_id          = $request->route('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $data             = array();
        $data['datetime'] = date('Y-m-d');
        //确定统计分表名称
        $last_num       = $store_id % 10; //获取店铺ID的末位数字
        $tablenum       = ($t = intval($flowstat_tablenum)) > 1 ? $t : 1; //处理流量统计记录表数量
        $flow_tablename = ($t = ($last_num % $tablenum)) > 0 ? "flowstat_$t" : 'flowstat';
        //今日开始和结束时间
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday   = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;

        //昨日开始和结束时间
        $beginYesterday = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
        $endYesterday   = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;


        //点击量
        $today_click = DB::table($flow_tablename)
            ->where('store_id', $store_id)
            ->whereBetween('stattime', [$beginToday, $endToday])
            ->first(
                array(
                    DB::raw('IFNULL(SUM(clicknum),0) as clicknum')
                )
            );
        $yest_click  = DB::table($flow_tablename)
            ->where('store_id', $store_id)
            ->whereBetween('stattime', [$beginYesterday, $endYesterday])
            ->first(
                array(
                    DB::raw('IFNULL(SUM(clicknum),0) as clicknum')
                )
            );
        $field       = ['COUNT(*) as ordernum'];

        //订单
        $today_ordernum = DB::table('order')
            ->where('store_id', $store_id)
            ->whereBetween('add_time', [$beginToday, $endToday])
            ->first(
                array(
                    DB::raw('COUNT(*) as ordernum'),
                    DB::raw('IFNULL(SUM(order_amount),0) as orderamount')
                )
            );
        $yest_ordernum  = DB::table('order')
            ->where('store_id', $store_id)
            ->whereBetween('add_time', [$beginYesterday, $endYesterday])
            ->first(
                array(
                    DB::raw('COUNT(*) as ordernum'),
                    DB::raw('IFNULL(SUM(order_amount),0) as orderamount')
                )
            );

        if ($today_click->clicknum == 0) {
            $today_change = 0;
        } else {
            $today_change = $today_ordernum->ordernum / $today_click->clicknum;
        }
        if ($yest_click->clicknum == 0) {
            $yest_change = 0;
        } else {
            $yest_change = $yest_ordernum->ordernum / $yest_click->clicknum;
        }
        $data['store_id']            = $store_id;
        $data['today_click']         = $today_click->clicknum;
        $data['today_click_comp']    = $today_click->clicknum - $yest_click->clicknum;
        $data['today_ordernum']      = $today_ordernum->ordernum;
        $data['today_ordernum_comp'] = $today_ordernum->ordernum - $yest_ordernum->ordernum;
        $data['today_change']        = $today_change;
        $data['today_change_comp']   = $today_change - $yest_change;
        return view('store.jingying', ['data' => $data]);
    }

    public function getEcharts(Request $request)
    {
        $store_id = $request->input('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $data = $xday = $ydata = $result = array();
        for ($i = 7; $i > 0; $i--) {
            $data[$i]['start_time'] = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - $i * 3600 * 24;
            $data[$i]['end_time']   = $data[$i]['start_time'] + 24 * 3600;
            array_push($xday, date('Y-m-d', $data[$i]['start_time']));
        }

        $field = ['COUNT(*) as ordernum'];
        foreach ($data as $v) {
            $data = DB::table('order')
                ->where('store_id', $store_id)
                ->whereBetween('add_time', [$v['start_time'], $v['end_time']])
                ->first(
                    array(
                        DB::raw('COUNT(*) as ordernum'),
                    )
                );
            array_push($ydata, $data->ordernum);
        }
        $result['xday']  = $xday;
        $result['ydata'] = $ydata;
        return $result;

    }

    public function getEcharts_(Request $request)
    {
        $store_id = $request->input('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $data = $xday = $ydata = $result = array();
        for ($i = 7; $i > 0; $i--) {
            $data[$i]['start_time'] = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - $i * 3600 * 24;
            $data[$i]['end_time']   = $data[$i]['start_time'] + 24 * 3600;
            array_push($xday, date('Y-m-d', $data[$i]['start_time']));
        }

        foreach ($data as $v) {
            $data = DB::table('order')
                ->where('store_id', $store_id)
                ->whereBetween('add_time', [$v['start_time'], $v['end_time']])
                ->first(
                    array(
                        DB::raw('IFNULL(SUM(order_amount),0) as orderamount'),
                    )
                );
            array_push($ydata, $data->orderamount);
        }
        $result['xday']  = $xday;
        $result['ydata'] = $ydata;
        return $result;
    }

    public function getSMS(Request $request)
    {
        $phone_number = $request->input('phone_number');
        if (empty($phone_number)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!preg_match("/^1[34578]{1}\d{9}$/", $phone_number)) {
            return Base::jsonReturn(1000, '手机号格式不正确');
        }
        if (Redis::get($phone_number)) {
            $code = Redis::get($phone_number);
        } else {
            $code = rand('1000', '9999');
        }
        $res = SMSController::sendSms($phone_number, $code);

        if ($res->Code == 'OK') {
            Redis::setex($phone_number, 300, $code);
            return Base::jsonReturn(200, '发送成功');
        } else {
            return Base::jsonReturn(2000, '发送失败');
        }
    }

    /**添加银行卡
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBankAccount(Request $request)
    {
        $store_id       = $request->input('store_id');
        $account_name   = $request->input('account_name');
        $account_number = $request->input('account_number');
        $bank_name      = $request->input('bank_name');
        $bank_type      = $request->input('bank_type');

        if (!$store_id || !$account_name || !$account_number || !$bank_name || !$bank_type) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $member_id = BModel::getTableValue('store', ['store_id' => $store_id], 'member_id');
        $data      = array(
            'settlement_bank_account_name' => $account_name,
            'settlement_bank_account_number' => $account_number,
            'settlement_bank_name' => $bank_name,
            'settlement_bank_type' => $bank_type
        );
        $res       = BModel::upTableData('store_joinin', ['member_id' => $member_id], $data);
        if ($res) {
            return Base::jsonReturn(200, '添加成功');
        } else {
            return Base::jsonReturn(2000, '添加失败');
        }
    }

    /**银行卡列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bankAccountList(Request $request)
    {
        $store_id = $request->input('store_id');
        if (!$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $member_id = BModel::getTableValue('store', ['store_id' => $store_id], 'member_id');
        $data      = BModel::getTableFieldFirstData('store_joinin', ['member_id' => $member_id],
            ['settlement_bank_account_name as account_name', 'settlement_bank_account_number as account_number', 'settlement_bank_type as bank_type']);
        if ($data) {
            return Base::jsonReturn(200, '获取成功', $data);
        } else {
            return Base::jsonReturn(2000, '获取失败');
        }
    }

    /**银行卡详细信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bankAccountInfo(Request $request)
    {
        $store_id = $request->input('store_id');
        if (!$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $member_id = BModel::getTableValue('store', ['store_id' => $store_id], 'member_id');
        $data      = BModel::getTableFieldFirstData('store_joinin', ['member_id' => $member_id],
            ['settlement_bank_account_name as account_name', 'settlement_bank_account_number as account_number', 'settlement_bank_type as bank_type',
                'settlement_bank_name as bank_name', 'settlement_bank_address as bank_address']);
        if ($data) {
            return Base::jsonReturn(200, '获取成功', $data);
        } else {
            return Base::jsonReturn(2000, '获取失败');
        }
    }

    /**解绑银行卡
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delBankAccount(Request $request)
    {
        $store_id = $request->input('store_id');
        if (!$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $member_id = BModel::getTableValue('store', ['store_id' => $store_id], 'member_id');
        $data      = array(
            'settlement_bank_account_name' => '',
            'settlement_bank_account_number' => '',
            'settlement_bank_name' => '',
            'settlement_bank_type' => ''
        );
        $res       = BModel::upTableData('store_joinin', ['member_id' => $member_id], $data);
        if ($res) {
            return Base::jsonReturn(200, '解绑成功');
        } else {
            return Base::jsonReturn(2000, '解绑失败');
        }
    }

    /**财务结算
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeJieSuan(Request $request)
    {
        $store_id = $request->input('store_id');
        if (!$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $jiesuan           = Voucher::getJieSuan(['ob_store_id' => $store_id, 'ob_state' => 4]);//已结算
        $w_jiesuan         = Voucher::getJieSuan(['ob_store_id' => $store_id, 'ob_state' => ['in', [1, 2, 3]]]);//未结算
        $data              = [];
        $data['y_jiesuan'] = $jiesuan;
        $data['d_jiesuan'] = $w_jiesuan;
        $field             = ['ob_state', 'ob_no', 'os_month', 'ob_order_totals', 'ob_commis_totals', 'ob_order_return_totals', 'ob_commis_return_totals', 'ob_store_cost_totals'];
        $data['list']      = Voucher::getJieSuanOb(['ob_store_id' => $store_id], 'ob_no', 4, $field);
        $member_id         = BModel::getTableValue('store', ['store_id' => $store_id], 'member_id');
        $account           = BModel::getTableFieldFirstData('store_joinin', ['member_id' => $member_id], ['settlement_bank_type as bank_type', 'settlement_bank_account_number as account_number']);
        $data['account']   = empty($account) ? null : $account;
        $data['message']   = array(
            'addtime' => date('Y-m-d H:i:s'),
            'msg' => '可为免费空位欺负你委屈而烦恼为妇女'
        );
        return Base::jsonReturn(200, '获取成功', $data);
    }

    /**全部账单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function allStoreJieSuan(Request $request)
    {
        $store_id = $request->input('store_id');
        $year     = $request->input('year');
        if (!$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        if (preg_match('/^\d{4}$/', $year, $match)) {
            $condition['os_year'] = $year;
        } else {
            $condition['os_year'] = '2019';
        }
        $os_month = BModel::getTableAllData('order_statis', $condition, ['os_month']);
        if ($os_month->isEmpty()) {
            $data = null;
        } else {
            $os_month_list = [];
            $months        = $os_month->toArray();
            foreach ($months as $v) {
                $os_month_list[] = $v->os_month;
            }
            $field             = ['ob_state', 'ob_no', 'os_month', 'ob_order_totals', 'ob_commis_totals', 'ob_order_return_totals', 'ob_commis_return_totals', 'ob_store_cost_totals'];
            $data['list']      = Voucher::getAllJiesuanByYear($condition, $store_id, $field);
            $data['y_jiesuan'] = Voucher::getJieSuan(['ob_store_id' => $store_id, 'ob_state' => 4, 'os_month' => ['in', $os_month_list]]);//已结算
            $data['d_jiesuan'] = Voucher::getJieSuan(['ob_store_id' => $store_id, 'ob_state' => ['in', [1, 2, 3]], 'os_month' => ['in', $os_month_list]]);//未结算
        }
        return Base::jsonReturn(200, '获取成功', $data);
    }

    /**提现列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cashList(Request $request)
    {
        $store_id = $request->input('store_id');
        $keyword  = $request->input('keyword');
        if (!$store_id || !$keyword) {
            return Base::jsonReturn(1000, '参数缺失');
        }

        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $member_id            = BModel::getTableValue('store', ['store_id' => $store_id], 'member_id');
        $available_predeposit = BModel::getTableValue('member', ['member_id' => $member_id], 'available_predeposit');
        $time                 = explode('-', $keyword);
        $yue                  = $time[1];
        $nian                 = $time[0];
        $begin_time           = mktime(0, 0, 0, $yue, 1, $nian);
        $end_time             = mktime(23, 59, 59, ($yue + 1), 0, $nian);

        $field             = ['pdc_amount as amount', 'pdc_payment_state as payment_state', 'pdc_add_time as add_time', 'pdc_bank_no as bank_no'];
        $data              = Store::cashList($member_id, $begin_time, $end_time, $field);
        $result            = [];
        $result['data']    = empty($data) ? null : $data;
        $result['balance'] = $available_predeposit;;
        $result['total_amount'] = Store::getCashSum($member_id, $begin_time, $end_time, 'pdc_amount');
        return Base::jsonReturn(200, '获取成功', $result);
    }

    /**提现
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCash(Request $request)
    {
        $store_id = $request->input('store_id');
        $money    = Base::ncPriceFormat($request->input('money'));
        if (!$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $member_info          = BModel::getTableFirstData('store', ['store_id' => $store_id], ['member_id', 'member_name']);
        $available_predeposit = Voucher::getTableValue('member', ['member_id' => $member_info->member_id], 'available_predeposit');
        //验证支付密码
//        if (md5($_POST['password']) != $member_info['member_paypwd']) {
//            showDialog('支付密码错误','','error');
//        }
        //验证金额是否足够
        if ($money <= Base::ncPriceFormat(0)) {
            return Base::jsonReturn(2002, '提现金额不能为0');
        }
        if (floatval($available_predeposit) < floatval($money)) {
            return Base::jsonReturn(2001, '余额不足');
        }
        DB::transaction(function () use ($member_info, $money) {
            $account_info              = BModel::getTableFirstData('store_joinin', ['member_id' => $member_info->member_id], ['settlement_bank_account_name', 'settlement_bank_type', 'settlement_bank_account_number']);
            $pdc_sn                    = Store::makeSn($member_info->member_id);
            $data                      = array();
            $data['pdc_sn']            = $pdc_sn;
            $data['pdc_member_id']     = $member_info->member_id;
            $data['pdc_member_name']   = $member_info->member_name;
            $data['pdc_amount']        = $money;
            $data['pdc_bank_name']     = $account_info->settlement_bank_type;
            $data['pdc_bank_no']       = $account_info->settlement_bank_account_number;
            $data['pdc_bank_user']     = $account_info->settlement_bank_account_name;
            $data['pdc_add_time']      = time();
            $data['pdc_payment_state'] = '0';
            BModel::insertData('pd_cash', $data);
        });
        return Base::jsonReturn(200, '提现成功');
    }

    function joinin_Step1()
    {
        return view('store.joinin_step1');
    }

    /**入驻第一步
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function joininStep1(Request $request)
    {
        $param['member_id']                          = $request->input('member_id');
        $param['store_name']                         = $request->input('company_name');
        $param['contacts_name']                      = $request->input('contacts_name');
        $param['contacts_phone']                     = $request->input('contacts_phone');
        $param['company_address']                    = $request->input('company_address');
        $param['company_address_detail']             = $request->input('company_address_detail');
        $param['face_img']                           = $request->input('face_img');
        $param['store_img']                          = $request->input('store_img');
        $param['logo_img']                           = $request->input('logo_img');
        $param['business_sphere']                    = $request->input('business_sphere');
        $param['business_licence_number']            = $request->input('business_licence_number');
        $param['ID_card']                            = $request->input('ID_card');
        $param['business_licence_number_electronic'] = $request->input('business_licence_number_electronic');
        $param['sc_id']                              = $request->input('sc_id');
        $param['joinin_state']                       = 10;
        $province                                    = $request->input('province');
        $city                                        = $request->input('city');
        $country                                     = $request->input('country');
        $store_class_ids                             = json_decode($request->input('store_class_ids'));
        $store_class_names                           = json_decode($request->input('store_class_names'));
        $param['company_address']                    = $province . $city . $country;
        $param['company_address']                    = serialize(array($store_class_ids));
        $param['store_class_names']                  = serialize(array($store_class_names));
        $param['store_class_commis_rates']           = BModel::getTableValue('goods_class', ['gc_id' => $param['sc_id']], 'commis_rate');

        if (BModel::getCount('store_joinin', ['member_id' => $param['member_id']]) > 0) {
            return Base::jsonReturn(2000, '店铺已存在申请记录');
        }
        $member_name          = BModel::getTableValue('member', ['member_id' => $param['member_id']], 'member_name');
        $param['member_name'] = $member_name;
        $param['sc_name']     = BModel::getTableValue('store_class', ['sc_id' => $param['sc_id']], 'sc_name');
        $ins_id               = BModel::insertData('store_joinin', $param);
        if ($ins_id) {
            return Base::jsonReturn(200, '提交成功');
        } else {
            return Base::jsonReturn(2001, '提交失败');
        }
    }

    /**第二步
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function joininStep2(Request $request)
    {
        $member_id                         = $request->input('member_id');
        $param['paying_money_certificate'] = $request->input('paying_money_certificate');
        $param['paying_amount']            = $request->input('paying_amount');
        $param['joinin_state']             = 11;
        $res                               = BModel::upTableData('store_joinin', ['member_id' => $member_id], $param);
        if ($res) {
            return Base::jsonReturn(200, '提交成功');
        } else {
            return Base::jsonReturn(2001, '提交失败');
        }
    }

    static function joininMessage(Request $request)
    {
        $member_id = $request->input('member_id');
        if (!$member_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $data = BModel::getTableValue('store_joinin', ['member_id' => $member_id], 'joinin_message');
        return Base::jsonReturn(200, '获取成功', $data);
    }


    /**系统消息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function msgList(Request $request)
    {
        $store_id = $request->input('store_id');
        $page     = $request->input('page');
        if (!$store_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $page = !$page ? 1 : $page;
        $data = Store::msgList(['store_id' => $store_id], $page);
        return Base::jsonReturn(200, '获取成功', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function msgInfo(Request $request)
    {
        $store_id = $request->input('store_id');
        $sm_id    = $request->input('sm_id');
        if (!$store_id || !$sm_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $data             = Store::getmsgInfo(['sm_id' => $sm_id]);// BModel::getTableFieldFirstData('store_msg',['sm_id'=>$sm_id],['sm_id','sm_content','sm_addtime','sm_title']);
        $data->sm_addtime = date('Y-m-d H:i:s', $data->sm_addtime);
        if ($data) {
            if (BModel::getCount('store_msg_read', ['sm_id' => $sm_id]) == 0) {
                $condition              = array();
                $condition['seller_id'] = $store_id;
                $condition['sm_id']     = $sm_id;
                $condition['read_time'] = time();

                BModel::insertData('store_msg_read', $condition);

                $update               = array();
                $sm_readids[]         = $store_id;
                $update['sm_readids'] = implode(',', $sm_readids) . ',';
                BModel::upTableData('store_msg', ['sm_id' => $sm_id], $update);
            }
            return Base::jsonReturn(200, '获取成功', $data);
        } else {
            return Base::jsonReturn(2001, '获取失败');
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function areaList(Request $request)
    {
        if (!$data = json_decode(Redis::get('arealist'))) {
            $data = Store::getAreaList();
            Redis::set('arealist', json_encode($data));
        }

        return Base::jsonReturn(200, '获取成功', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function gcList(Request $request)
    {
        $data = Store::getGcList();
        if ($data) {
            return Base::jsonReturn(200, '获取成功', $data);
        } else {
            return Base::jsonReturn(2001, '获取失败');
        }
    }

    /**更换头像
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    static function changeAvator(Request $request)
    {
        $store_id = $request->input('store_id');
        $avator   = $request->input('avator');
        if (!$store_id || !$avator) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $member_info = BModel::getTableFirstData('store', ['store_id' => $store_id], ['member_id', 'member_name']);
        $data        = Store::upTableData('store', ['store_id' => $store_id], ['store_avatar' => $avator]);
        if ($data) {
            $field                                    = ['a.store_id', 'a.store_name', 'a.store_phone', 'a.store_avatar',
                'a.area_info', 'a.store_address', 'a.work_start_time', 'a.work_end_time',
                'a.store_state', 'a.store_description', 'a.work_start_time', 'a.work_end_time',
                'b.business_licence_number_electronic',
                'c.member_id', 'c.member_name', 'c.member_mobile'];
            $data                                     = Store::getStoreAndJoinInfo(['a.member_id' => $member_info->member_id], $field);
            $data->business_licence_number_electronic = getenv('WEB_URL') . 'upload/shop/store_joinin/06075408577995264.png';
            $data->token                              = Base::makeToken($data->store_id, $member_info->member_name);
            return Base::jsonReturn(200, '获取成功', $data);
        } else {
            return Base::jsonReturn(2001, '获取失败');
        }
    }

    /**设置自动接单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    static function autoReceiveOrder(Request $request)
    {
        $store_id = $request->input('store_id');
        $is_open  = $request->input('is_open');
        if (!$store_id || !$is_open) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!Base::checkStoreExist($store_id)) {
            return Base::jsonReturn(2000, '商家不存在');
        }
        $res = BModel::upTableData('store', ['store_id' => $store_id], ["auto_receive_order" => intval($is_open)]);
        if ($res) {
            return Base::jsonReturn(200, '设置成功');
        } else {
            return Base::jsonReturn(2001, '设置失败');
        }
    }
}
