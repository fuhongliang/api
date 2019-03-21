<?php
namespace app\v1\controller;
use app\v1\model\Order as OrderModel;
use app\v1\model\Store as StoreModel;
use think\Controller;
use think\Request;
use app\v1\model\Sms as SMSmodel;
use think\facade\Cache;
use app\v1\model\Member as MemberModel;
use app\v1\model\Goods as GoodsModel;
use app\v1\controller\Base as Base;
use think\Db;
/**
 * Class Order  店铺
 * @package app\v1\controller
 */
class Store extends Controller
{
    /** 添加或更新分类
     * @param Request $request
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function addStoreGoodsClass(Request $request)
    {
        $class_id = $request->param('class_id');
        $store_id = $request->param('store_id');
        $class_name = $request->param('class_name');
        if (empty($store_id) || empty($class_name)) {
            return Base::jsonReturn(1000, null, '参数缺失');
        }
        if(!$class_id)
        {
            $is_exist = StoreModel::checkStoreGoodsClassExist($store_id, $class_name);
            if ($is_exist) {
                $ins_data = array(
                    'stc_name' => $class_name,
                    'store_id' => $store_id,
                    'stc_parent_id' => 0,
                    'stc_state' => 1,
                    'stc_sort' => 0
                );
                $res = StoreModel::addStoreGoodsClass($ins_data);
                if ($res) {
                    return Base::jsonReturn(200, null, '新增成功');
                } else {
                    return Base::jsonReturn(2000, null, '新增失败');
                }
            } else {
                return Base::jsonReturn(2000, null, '名称已存在');
            }
        }else{
            //存在检测重名
            $store_info=StoreModel::getStoreClassInfo(['store_id'=>$store_id,'stc_name'=>$class_name]);
            if (empty($store_info))
            {
                $res = StoreModel::editStoreClassInfo(['stc_id'=>$class_id],['stc_name'=>$class_name]);
                if ($res) {
                    return Base::jsonReturn(200, null, '更新成功');
                } else {
                    return Base::jsonReturn(2000, null, '更新失败');
                }
            }elseif ( $class_id == $store_info['stc_id'])
            {
                return Base::jsonReturn(200, null, '更新成功');
            }else{
                return Base::jsonReturn(2000, null, '名称已存在');
            }
        }

    }

    /**
     * @param Request $request
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delStoreGoodsClass(Request $request)
    {
        $class_id = $request->param('class_id');
        $store_id = $request->param('store_id');
        if (empty($class_id) || empty($store_id)) {
            return Base::jsonReturn(1000, null, '参数缺失');
        }
        $res=StoreModel::delStoreClassInfo(['stc_id'=>$class_id,'store_id'=>$store_id]);
        if ($res) {
            return Base::jsonReturn(200, null, '删除成功');
        } else {
            return Base::jsonReturn(2000, null, '删除失败');
        }
    }

    /** 商品分类列表
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function storeGoodsClassList(Request $request)
    {
        $store_id = $request->param('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, null, '参数缺失');
        }
        $data=StoreModel::getAllStoreClass(['store_id'=>$store_id],['stc_id,stc_name,stc_sort']);
        return Base::jsonReturn(200, $data, '获取成功');
    }


    /** 分类排序
     * @param Request $request
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function sortStoreGoodsClass(Request $request)
    {
        $class_ids = json_decode($request->param('class_ids'));
        $store_id = $request->param('store_id');
        if (empty($class_ids) || empty($store_id)) {
            return Base::jsonReturn(1000, null, '参数缺失');
        }
        $res=StoreModel::sortStoreGoodsClass($class_ids,$store_id);
        if ($res) {
            return Base::jsonReturn(200, null, '排序成功');
        } else {
            return Base::jsonReturn(2000, null, '排序失败');
        }
    }

    /** 商品列表
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function storeGoodsList(Request $request)
    {
        $class_id = $request->param('class_id');
        $store_id = $request->param('store_id');
        if (empty($class_id) || empty($store_id)) {
            return Base::jsonReturn(1000, null, '参数缺失');
        }
        $result=array();
        $result['class_id']=StoreModel::getAllStoreClass(['store_id'=>$store_id],['stc_id,stc_name']);
        $result['goods_list']=StoreModel::getStoreGoodsListByStcId($store_id,$class_id);
        return Base::jsonReturn(200, $result, '获取成功');
    }

    /** 店铺概况
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStoreSetting(Request $request)
    {
        $store_id = $request->param('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, null, '参数缺失');
        }
        $data=StoreModel::getStoreData(['a.store_id'=>$store_id], ['a.store_state,a.store_description,a.store_label,a.store_phone,
a.area_info,a.store_address,a.store_workingtime,b.business_licence_number_electronic']);
        $data['store_zizhi']='http://master.shop.ifhu.cn/data/upload/shop/store/slide/f01.jpg';

        $field= 'a.store_id,a.store_name,IFNULL(a.store_avatar,"") as store_avatar,a.work_start_time,a.work_end_time,c.member_id,IFNULL(c.member_mobile,"") as member_mobile';
        $result=StoreModel::getStoreAndJoinInfo($field);
        $data['store_id']=$result['store_id'];
        $data['store_name']=$result['store_name'];
        $data['store_avatar']=$result['store_avatar'];
        $data['work_start_time']=$result['work_start_time'];
        $data['work_end_time']=$result['work_end_time'];
        $data['member_id']=$result['member_id'];
        $data['member_mobile']=$result['member_mobile'];

        return Base::jsonReturn(200, $data, '获取成功');
    }

    /** 设置店铺状态
     * @param Request $request
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function setWorkState(Request $request)
    {
        $store_id = $request->param('store_id');
        $store_state = $request->param('store_state');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, null, '参数缺失');
        }
        $res=StoreModel::setWorkState(['store_id'=>$store_id],['store_state'=>$store_state]);
        if ($res) {
            return Base::jsonReturn(200, null, '设置成功');
        } else {
            return Base::jsonReturn(2000, null, '设置失败');
        }
    }

    /** 设置公告
     * @param Request $request
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function setStoreDesc(Request $request)
    {
        $store_id = $request->param('store_id');
        $store_desc = $request->param('store_desc');
        if (empty($store_id) || empty($store_desc)) {
            return Base::jsonReturn(1000, null, '参数缺失');
        }
        $res=StoreModel::setWorkState(['store_id'=>$store_id],['store_description'=>$store_desc]);
        if ($res) {
            return Base::jsonReturn(200, null, '设置成功');
        } else {
            return Base::jsonReturn(2000, null, '设置失败');
        }
    }

    /** 店铺设置电话
     * @param Request $request
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function setStorePhone(Request $request)
    {
        $store_id = $request->param('store_id');
        $phone_number = $request->param('phone_number');
        if (empty($store_id) || empty($phone_number)) {
            return Base::jsonReturn(1000, null, '参数缺失');
        }
        $res=StoreModel::setWorkState(['store_id'=>$store_id],['store_phone'=>$phone_number]);
        if ($res) {
            return Base::jsonReturn(200, null, '设置成功');
        } else {
            return Base::jsonReturn(2000, null, '设置失败');
        }
    }

    /** 修改营业时间
     * @param Request $request
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function setStoreWorkTime(Request $request)
    {
        $store_id = $request->param('store_id');
        $work_start_time = $request->param('work_start_time');
        $work_end_time = $request->param('work_end_time');
        if (empty($store_id) || empty($work_start_time) || empty($work_end_time)) {
            return Base::jsonReturn(1000, null, '参数缺失');
        }
        $res=StoreModel::setWorkState(['store_id'=>$store_id],['work_start_time'=>$work_start_time,'work_end_time'=>$work_end_time]);
        if ($res) {
            return Base::jsonReturn(200, null, '设置成功');
        } else {
            return Base::jsonReturn(2000, null, '设置失败');
        }

    }

    /** 意见反馈
     * @param Request $request
     * @return array
     */
    public function msgFeedBack(Request $request)
    {
        $store_id = $request->param('store_id');
        $content = $request->param('content');
        $type = $request->param('type');// 1 安卓 2 ios
        if (empty($store_id) || empty($content) || empty($type)) {
            return Base::jsonReturn(1000, null, '参数缺失');
        }
        $data=array(
            'store_id'=>$store_id,
            'content'=>$content,
            'ftime'=>time(),
            'type'=>$type
        );
        $res=StoreModel::addAppFeedBack($data);
        if ($res) {
            return Base::jsonReturn(200, null, '反馈成功');
        } else {
            return Base::jsonReturn(2000, null, '反馈失败');
        }

    }

    /** 发送短信
     * @param Request $request
     * @return array
     */
    public function getSMS(Request $request)
    {
        $phone_number = $request->param('phone_number');
        if (empty($phone_number)) {
            return Base::jsonReturn(1000, null, '参数缺失');
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$phone_number))
        {
            return Base::jsonReturn(1000, null, '手机号格式不正确');
        }
        $code=rand('1000','9999');
        $res=SMSModel::sendSms($phone_number,'SMS_160861509',$code);
        var_dump($res);die;
        if ($res->Message == 'OK') {
            Cache::set($phone_number,$code,300);
            return Base::jsonReturn(200, null, '发送成功');
        } else {
            return Base::jsonReturn(2000, null, '发送失败');
        }
    }

    /** 重置密码
     * @param Request $request
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function editPasswd(Request $request)
    {
        $member_id = $request->param('member_id');
        $phone_number = $request->param('phone_number');
        $verify_code = $request->param('verify_code');
        $new_passwd = $request->param('new_passwd');
        $con_new_passwd = $request->param('con_new_passwd');
        if (empty($member_id) || empty($verify_code) || empty($new_passwd) || empty($con_new_passwd)) {
            return Base::jsonReturn(1000, null ,'参数缺失');
        }
        if($new_passwd !==$con_new_passwd)
        {
            return Base::jsonReturn(2001, null, '密码不一致');
        }
        if(strlen(trim($new_passwd))<=6)
        {
            return Base::jsonReturn(2002, null, '密码最少6位');
        }
        $code=Cache::get($phone_number);
        if(!$code || $code !== $verify_code)
        {
            return Base::jsonReturn(2003, null, '验证码错误');
        }else{
            $res=MemberModel::editMemberInfo(['member_id'=>$member_id],['member_passwd'=>md5($new_passwd)]);
            if ($res) {
                return Base::jsonReturn(200, null, '修改成功');
            } else {
                return Base::jsonReturn(2000, null, '修改失败');
            }
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStoreCom(Request $request)
    {
        $store_id = $request->param('store_id');
        $haoping = $request->param('haoping');// 1 好评  2 中评 3 差评
        $no_com = $request->param('no_com');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, null ,'参数缺失');
        }
        $result=array();
        if(!empty($no_com))//未回复
        {
            $condition=['store_id'=>$store_id,'parent_id'=>0,'is_replay'=>0];
        }else{//全部
            $result['haping']=StoreModel::getComNums($store_id);
            if(!$haoping)
            {
                $condition=['store_id'=>$store_id];
            }else{
                $condition=['store_id'=>$store_id,'haoping'=>$haoping];
            }
        }
        $result['com_list']=StoreModel::getStoreComAllData($condition);
        return Base::jsonReturn(200, $result, '获取成功');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function storeFeedback(Request $request)
    {
        $store_id = $request->param('store_id');
        $content = $request->param('content');
        $parent_id = $request->param('parent_id');
        if (empty($store_id) || empty($content) || empty($parent_id)) {
            return Base::jsonReturn(1000, null ,'参数缺失');
        }
        $ins_data=array(
            'store_id'=>$store_id,
            'content'=>$content,
            'parent_id'=>$parent_id,
            'add_time'=>time()
        );
        Db::transaction(function () use ($ins_data,$parent_id){
            StoreModel::addStoreCom($ins_data);
            StoreModel::upStoreCom(['com_id' => $parent_id], ['is_replay'=>1]);
        });
        return Base::jsonReturn(200, null, '回复成功');
    }


    /**
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function storeYunYingInfo(Request $request)
    {
        $store_id = $request->param('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, null ,'参数缺失');
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
        $where1 = [
            'add_time'   => ['between', [$stime,$etime]],
            'store_id' => $store_id,
        ];
        $where2 = [
            'add_time'   => ['between', [$beginToday,$endToday]],
            'store_id' => $store_id,
        ];
        // 30天 下单量和销售金额
        $field=['COUNT(*) as ordernum, IFNULL(SUM(order_amount),0) as orderamount'];

        $data=OrderModel::getOrderYunYing($where1,$field);
        //店铺收藏量 商品数量
        $store_collect_data= StoreModel::getStoreInfo(['store_id'=>$store_id],['store_collect']);
        $goods_num=GoodsModel::getGoodsCount(['store_id'=>$store_id],'goods_id');
        $data2=OrderModel::getOrderYunYing($where2, $field);
        $result=array();
        $result['today_ordernum']=$data2['ordernum'];
        $result['today_orderamount']=$data2['orderamount'];
        $result['30_ordernum']=$data['ordernum'];
        $result['30_orderamount']=$data['orderamount'];
        $result['store_collect']=$store_collect_data['store_collect'];
        $result['goods_num']=$goods_num;
        return Base::jsonReturn(200, $result, '获取成功');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function storeJingYingData(Request $request)
    {
        $store_id = $request->param('store_id');
        $this->assign('store_id',$store_id);
        return $this->fetch();
    }

    /**
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getEcharts(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
        $store_id = $request->param('store_id');
        if (empty($store_id)) {
            return Base::jsonReturn(1000, null ,'参数缺失');
        }
        $data=$xday=$ydata=$result=array();
        for ($i=0;$i<7;$i++)
        {
            $data[$i]['start_time']=mktime(0,0,0,date('m'),date('d'),date('Y'))-$i*3600*24 ;
            $data[$i]['end_time']=$data[$i]['start_time']+24*3600;
            array_push($xday,date('Y-m-d',$data[$i]['start_time']));
        }

        $field=['COUNT(*) as ordernum'];
        foreach ($data as $v)
        {
            $where =[
                'add_time'   => ['between', [$v['start_time'],$v['end_time']]],
                'store_id' => $store_id
            ];
            $data=OrderModel::getOrderYunYing($where,$field);
            array_push($ydata,$data['ordernum']);
        }
        $result['xday']=$xday;
        $result['ydata']=$ydata;
        return $result;

    }

}