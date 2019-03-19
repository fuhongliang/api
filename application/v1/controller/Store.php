<?php
namespace app\v1\controller;
use app\v1\controller\Base;
use app\v1\model\Order as OrderModel;
use app\v1\model\Store as StoreModel;
use think\Request;

/**
 * Class Order  店铺
 * @package app\v1\controller
 */
class Store extends Base
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
            return Base::jsonReturn(1000, [], '参数缺失');
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
                    return Base::jsonReturn(200, [], '新增成功');
                } else {
                    return Base::jsonReturn(2000, [], '新增失败');
                }
            } else {
                return Base::jsonReturn(2000, [], '名称已存在');
            }
        }else{
            //存在检测重名
            $store_info=StoreModel::getStoreClassInfo(['store_id'=>$store_id,'stc_name'=>$class_name]);
            if (empty($store_info))
            {
                $res = StoreModel::editStoreClassInfo(['stc_id'=>$class_id],['stc_name'=>$class_name]);
                if ($res) {
                    return Base::jsonReturn(200, [], '更新成功');
                } else {
                    return Base::jsonReturn(2000, [], '更新失败');
                }
            }elseif ( $class_id == $store_info['stc_id'])
            {
                return Base::jsonReturn(200, [], '更新成功');
            }else{
                return Base::jsonReturn(2000, [], '名称已存在');
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
            return Base::jsonReturn(1000, [], '参数缺失');
        }
        $res=StoreModel::delStoreClassInfo(['stc_id'=>$class_id,'store_id'=>$store_id]);
        if ($res) {
            return Base::jsonReturn(200, [], '删除成功');
        } else {
            return Base::jsonReturn(2000, [], '删除失败');
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
            return Base::jsonReturn(1000, [], '参数缺失');
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
            return Base::jsonReturn(1000, [], '参数缺失');
        }
        $res=StoreModel::sortStoreGoodsClass($class_ids,$store_id);
        if ($res) {
            return Base::jsonReturn(200, [], '排序成功');
        } else {
            return Base::jsonReturn(2000, [], '排序失败');
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
            return Base::jsonReturn(1000, [], '参数缺失');
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
            return Base::jsonReturn(1000, [], '参数缺失');
        }
        $data=StoreModel::getStoreData(['a.store_id'=>$store_id], ['a.store_state,a.store_description,a.store_label,a.store_phone,
a.area_info,a.store_address,a.store_workingtime,b.business_licence_number_electronic']);
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
        if (empty($store_id) || empty($store_state)) {
            return Base::jsonReturn(1000, [], '参数缺失');
        }
        $res=StoreModel::setWorkState(['store_id'=>$store_id],['store_state'=>$store_state]);
        if ($res) {
            return Base::jsonReturn(200, [], '设置成功');
        } else {
            return Base::jsonReturn(2000, [], '设置失败');
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
            return Base::jsonReturn(1000, [], '参数缺失');
        }
        $res=StoreModel::setWorkState(['store_id'=>$store_id],['store_description'=>$store_desc]);
        if ($res) {
            return Base::jsonReturn(200, [], '设置成功');
        } else {
            return Base::jsonReturn(2000, [], '设置失败');
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
            return Base::jsonReturn(1000, [], '参数缺失');
        }
        $res=StoreModel::setWorkState(['store_id'=>$store_id],['store_phone'=>$phone_number]);
        if ($res) {
            return Base::jsonReturn(200, [], '设置成功');
        } else {
            return Base::jsonReturn(2000, [], '设置失败');
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
            return Base::jsonReturn(1000, [], '参数缺失');
        }
        $res=StoreModel::setWorkState(['store_id'=>$store_id],['work_start_time'=>$work_start_time,'work_end_time'=>$work_end_time]);
        if ($res) {
            return Base::jsonReturn(200, [], '设置成功');
        } else {
            return Base::jsonReturn(2000, [], '设置失败');
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
            return Base::jsonReturn(1000, [], '参数缺失');
        }
        $data=array(
            'store_id'=>$store_id,
            'content'=>$content,
            'ftime'=>time(),
            'type'=>$type
        );
        $res=StoreModel::addAppFeedBack($data);
        if ($res) {
            return Base::jsonReturn(200, [], '反馈成功');
        } else {
            return Base::jsonReturn(2000, [], '反馈失败');
        }

    }






}