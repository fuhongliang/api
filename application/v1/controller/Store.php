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
        if (empty($class_id) || empty($class_name)) {
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

    /**
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

}