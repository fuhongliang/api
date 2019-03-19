<?php
namespace app\v1\model;
use think\Model;
use think\DB;
use app\v1\model\Goods as GoodsModel;
/**
 * Class Member
 * @package app\v1\model 商家模型
 */
class Store extends Model{
    protected $pk = 'store_id';

    /** 获取商家店铺详情
     * @param $condition
     * @param string $field
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getStoreInfo($condition, $field = '*')
    {
        return Db::name('store')->field($field)->where($condition)->find();
    }

    /** 获取店铺以及申请信息
     * @param string $field
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function  getStoreAndJoinInfo($field = '*')
    {
        return Db::name('store')
            ->alias('a')
            ->join('store_joinin b','a.member_id = b.member_id')
            ->join('member c','a.member_id = c.member_id')
            ->field($field)
            ->find();
    }

    /**
     * @param $ins_data
     * @return int|string
     */
    static function addStoreGoodsClass($ins_data)
    {
        return Db::name('store_goods_class')->insert($ins_data);
    }

    /**
     * @param $store_id
     * @param $class_name
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function checkStoreGoodsClassExist($store_id,$class_name)
    {
        $data=Db::name('store_goods_class')
            ->where('store_id',$store_id)
            ->where('stc_name',$class_name)
            ->find();
        return empty($data)?true:false;
    }

    /**
     * @param $condition
     * @param string $field
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getStoreClassInfo($condition, $field = '*')
    {
        return Db::name('store_goods_class')->field($field)->where($condition)->find();
    }

    /**
     * @param $condition
     * @param string $field
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getAllStoreClass($condition, $field = '*',$order ='stc_sort desc')
    {
        return Db::name('store_goods_class')
            ->field($field)
            ->where($condition)
            ->order($order)
            ->select();
    }
    /**
     * @param $condition
     * @param $up_data
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static function editStoreClassInfo($condition,$up_data)
    {
        return Db::name('store_goods_class')
            ->where($condition)
            ->update($up_data);
    }

    /**
     * @param $condition
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static function delStoreClassInfo($condition)
    {
        return Db::name('store_goods_class')->where($condition)->delete();
    }

    /**
     * @param $store_id
     * @param $class_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getStoreGoodsListByStcId($store_id,$class_id)
    {
        $goods_info=$ids=array();
        $data=Db::name('goods')
            ->field(['goods_id,goods_stcids'])
            ->where('store_id',$store_id)
            ->whereNotNull('goods_stcids')
            ->select();
        if(empty($data))
        {
            return $goods_info;
        }else{
            foreach ($data as $val)
            {
                if(!empty($val['goods_stcids']))
                {
                    $stcids=explode(',',$val['goods_stcids']);
                    if(in_array($class_id,$stcids))
                    {
                        array_push($ids,$val['goods_id']);
                    }
                }
            }
            if(!empty($ids))
            {
                foreach ($ids as $k=>$goods_id)
                {
                    $fields=['goods_id,goods_name,goods_price,goods_marketprice,goods_salenum,goods_storage'];
                    $goods_info[$k]=GoodsModel::getGoodsInfo(['goods_id'=>$goods_id],$fields);
                }
            }
            return $goods_info;
        }
    }

    /**
     * @param $class_ids
     * @param $store_id
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static function sortStoreGoodsClass($class_ids,$store_id)
    {
        if(!empty($class_ids))
        {
            foreach ($class_ids as $k=>$id)
            {
                $res=self::upStoreGoodsClassSort(['stc_id'=>$id,'store_id'=>$store_id],['stc_sort'=>$k]);
                if(!$res)
                {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * @param $condition
     * @param $up_data
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static function upStoreGoodsClassSort($condition,$up_data)
    {
        return Db::name('store_goods_class')
            ->where($condition)
            ->update($up_data);
    }

    /**
     * @param $condition
     * @param string $field
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getStoreBindClass($condition, $field = '*')
    {
        return Db::name('store_bind_class')->field($field)->where($condition)->find();
    }

    /**
     * @param $condition
     * @param string $field
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getStoreData($condition, $field = '*')
    {
        $result=array();
        $data=Db::name('store')
            ->alias('a')
            ->leftJoin('store_joinin b', 'a.member_id = b.member_id')
            ->field($field)
            ->where($condition)
            ->find();
        if (!empty($data))
        {
            $result['store_state']=self::getStoreState($data['store_state']);
            $result['store_desc']=$data['store_description'];
            $result['store_logo']=$data['store_label'];
            $result['store_phone']=$data['store_phone'];
            $result['address']=$data['area_info'].$data['store_address'];
            $result['store_zizhi']=$data['business_licence_number_electronic'];
        }
        return $result;
    }

    /**
     * @param $state
     * @return string
     */
    static function getStoreState($state)
    {
        if($state == 0)
        {
            return '关闭中';
        }elseif ($state == 1)
        {
            return '开启中';
        }else{
            return '审核中';
        }
    }

    /**
     * @param $condition
     * @param $up_data
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static function setWorkState($condition, $up_data)
    {
        return Db::name('store')
            ->where($condition)
            ->update($up_data);
    }

    /**
     * @param $data
     * @return int|string
     */
    static function addAppFeedBack($data)
    {
        return  Db::name('app_feedback')->insertGetId($data);
    }

    /**
     * @param $condition
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getStoreComAllData($condition)
    {
        $result=array();
        $data=Db::name('store_com')
            ->alias('a')
            ->field('a.*,b.member_avatar,b.member_name')
            ->leftJoin('member b','a.member_id=b.member_id')
            ->where($condition)
            ->select();
        if(!empty($data))
        {
            foreach ($data as $k=>$v)
            {
                $result[$k]['com_id']=$v['com_id'];
                $result[$k]['content']=$v['content'];
                $result[$k]['haoping']=$v['haoping'];
                $result[$k]['kouwei']=$v['kouwei'];
                $result[$k]['baozhuang']=$v['baozhuang'];
                $result[$k]['peisong']=$v['peisong'];
                $result[$k]['add_time']=date('Y-m-d H:i:s',$v['add_time']);
                $result[$k]['member_avatar']=$v['member_avatar'];
                $result[$k]['member_name']=$v['member_name'];
                $result[$k]['replay']=null;
                if($v['is_replay'] == 1)
                {
                    $result[$k]['replay']=self::getComReplay($v['parent_id']);
                }
            }
        }
        return $result;

    }

    /**
     * @param $parent_id
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function getComReplay($com_id)
    {
        $data=Db::name('store_com')
            ->where(['parent_id'=>$com_id])
            ->value('content');
        return $data;
    }

    /**
     * @param $store_id
     * @return array
     */
    static function getComNums($store_id)
    {
        $result=array();
        $result['all']=self::getHaopingNumsByType(['store_id'=>$store_id]);
        $result['haoping']=self::getHaopingNumsByType(['store_id'=>$store_id,'haoping'=>1]);
        $result['zhongping']=self::getHaopingNumsByType(['store_id'=>$store_id,'haoping'=>2]);
        $result['chaping']=self::getHaopingNumsByType(['store_id'=>$store_id,'haoping'=>3]);
        $result['rate']=0;
        if($result['all'] !== 0)
        {
            $result['rate']=ceil($result['haoping']/$result['all']);
        }
        return $result;
    }

    /**
     * @param $condition
     * @return float|string
     */
    static function getHaopingNumsByType($condition)
    {
        return Db::name('store_com')->where($condition)->count();
    }

}
