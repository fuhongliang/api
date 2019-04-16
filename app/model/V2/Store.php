<?php

namespace App\model\V2;

use App\Http\Controllers\BaseController as Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
    /**
     * @param $condition
     * @return mixed
     */
    static function getStoreInfo($condition)
    {
       return DB::table('store')->where($condition)->first();
    }

    /**
     * @param $condition
     * @param array $field
     * @return mixed
     */
    static function  getStoreAndJoinInfo($condition,$field = ['*'])
    {
        return DB::table('store as a')
            ->where($condition)
            ->leftJoin('store_joinin as b','a.member_id' ,'b.member_id')
            ->leftJoin('member as c','a.member_id' ,'c.member_id')
            ->get($field)
            ->first();
    }
    static function checkStoreGoodsClassExist($store_id,$class_name)
    {
        $data=DB::table('store_goods_class')
            ->where('store_id',$store_id)
            ->where('stc_name',$class_name)
            ->first();
        return empty($data)?true:false;
    }
    static function addStoreGoodsClass($ins_data)
    {
        return DB::table('store_goods_class')->insert($ins_data);
    }
    static function getAllStoreClass($condition, $field = ['*'])
    {
        return DB::table('store_goods_class')
            ->where($condition)
            ->orderBy('stc_sort','asc')
            ->get($field);
    }
    static function getStoreClassInfo($condition, $field = ['*'])
    {
        return DB::table('store_goods_class')->where($condition)->get($field)->first();
    }
    static function editStoreClassInfo($condition,$up_data)
    {
        return DB::table('store_goods_class')
            ->where($condition)
            ->update($up_data);
    }
    static function delStoreClassInfo($class_id,$store_id)
    {
        DB::transaction(function () use ($class_id,$store_id){
            DB::table('store_goods_class')->where(['stc_id'=>$class_id,'store_id'=>$store_id])->delete();
            DB::table('goods')->where('goods_stcid',$class_id)->delete();
            DB::table('goods_common')->where('goods_stcid',$class_id)->delete();
        });
        return true;
    }
    static function sortStoreGoodsClass($class_ids,$store_id)
    {
        if(!empty($class_ids))
        {
            foreach ($class_ids as $k=>$id)
            {
                self::upStoreGoodsClassSort(['stc_id'=>$id,'store_id'=>$store_id],['stc_sort'=>$k]);
            }
            return true;
        }
        return false;
    }
    static function upStoreGoodsClassSort($condition,$up_data)
    {
        return DB::table('store_goods_class')
            ->where($condition)
            ->update($up_data);
    }
    static function getStoreClassStcId($condition, $field =['*'])
    {
        $res= DB::table('store_goods_class')
            ->where($condition)
            ->orderBy('stc_sort','asc')
            ->get($field)
            ->first();
        return $res;
    }
    static function getStoreGoodsListByStcId($store_id,$class_id)
    {
        $goods_info=$ids=array();
        $data=DB::table('goods')
            ->where('store_id',$store_id)
            ->whereNotNull('goods_stcids')
            ->get(['goods_id','goods_stcids']);
        if(empty($data))
        {
            return $goods_info;
        }else{
            foreach ($data as $val)
            {
                if(!empty($val->goods_stcids))
                {
                    $stcids=explode(',',$val->goods_stcids);
                    if(in_array($class_id,$stcids))
                    {
                        array_push($ids,$val->goods_id);
                    }
                }
            }
            if(!empty($ids))
            {
                foreach ($ids as $k=>$goods_id)
                {
                    $fields=['a.goods_id','a.goods_name','a.goods_price','a.goods_marketprice','b.goods_body as goods_desc','b.goods_sale_time','a.goods_state','a.goods_storage','a.goods_image as img_name'];
                    $goods_info[$k]=Goods::getGoodsInfo(['goods_id'=>$goods_id],$fields);
                    $goods_info[$k]->img_path=getenv('GOODS_IMAGE').$store_id;
                    $goods_info[$k]->goods_sale_time=unserialize($goods_info[$k]->goods_sale_time);
                }
            }
            return $goods_info;
        }
    }
    static function getStoreBindClass($condition, $field = ['*'])
    {
        return DB::table('store_bind_class')
            ->where($condition)
            ->get($field)
            ->first();
    }
    static function getStoreData($condition, $field = ['*'])
    {
        $result=array();
        $data=DB::table('store as a')
            ->leftJoin('store_joinin as b', 'a.member_id', 'b.member_id')
            ->where($condition)
            ->get($field)
            ->first();
        if (!empty($data))
        {
            $result['store_state']=self::getStoreState($data->store_state);
            $result['store_desc']=$data->store_description;
            $result['store_logo']=$data->store_label;
            $result['store_phone']=$data->store_phone;
            $result['address']=$data->area_info.$data->store_address;
            $result['store_zizhi']=$data->business_licence_number_electronic;
        }
        return $result;
    }
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
    static function setWorkState($condition, $up_data)
    {
        return DB::table('store')
            ->where($condition)
            ->update($up_data);
    }
    static function  getStoreMemInfo($condition,$field = ['*'])
    {
        return DB::table('store as a')
            ->where($condition)
            ->leftJoin('member as c','a.member_id', 'c.member_id')
            ->get($field)
            ->first();
    }
    static function addAppFeedBack($data)
    {
        return  DB::table('mb_feedback')->insertGetId($data);
    }
    static function getComNums($store_id)
    {
        $result=array();
        $result['all']=self::getPingNumsByType(['store_id'=>$store_id]);
        $result['haoping']=self::getPingNumsByType(['store_id'=>$store_id,'haoping'=>1]);
        $result['zhongping']=self::getPingNumsByType(['store_id'=>$store_id,'haoping'=>2]);
        $result['chaping']=self::getPingNumsByType(['store_id'=>$store_id,'haoping'=>3]);
        $result['rate']=0;
        if($result['all'] !== 0)
        {
            $result['rate']=ceil($result['haoping']/$result['all']);
        }
        return $result;
    }
    static function getPingNumsByType($condition)
    {
        return DB::table('store_com')->where($condition)->count();
    }
    static function getStoreComAllData($condition)
    {
        $result=array();
        $data=DB::table('store_com as a')
            ->where($condition)
            ->leftJoin('member as b','a.member_id','b.member_id')
            ->get(['a.*','b.member_avatar','b.member_name']);
        if(!empty($data))
        {
            foreach ($data as $k=>$v)
            {
                $result[$k]['com_id']=$v->com_id;
                $result[$k]['content']=$v->content;
                $result[$k]['haoping']=$v->haoping;
                $result[$k]['kouwei']=$v->kouwei;
                $result[$k]['baozhuang']=$v->baozhuang;
                $result[$k]['peisong']=$v->peisong;
                $result[$k]['add_time']=date('Y-m-d H:i:s',$v->add_time);
                $result[$k]['member_avatar']=$v->member_avatar;
                $result[$k]['member_name']=$v->member_name;
                $result[$k]['replay']=null;
                if($v->is_replay == 1)
                {
                    $result[$k]['replay']=self::getComReplay(['parent_id'=>$v->com_id]);
                }
            }
        }
        return $result;

    }
    static function getComReplay($condition)
    {
        $data=DB::table('store_com')
            ->where($condition)
            ->value('content');
        return $data;
    }
    static function addStoreCom($data)
    {
        return  DB::table('store_com')->insertGetId($data);
    }
    static function upStoreCom($condition, $up_data)
    {
        return DB::table('store_com')
            ->where($condition)
            ->update($up_data);
    }
    static function getStoreField($condition,$field)
    {
        return DB::table('store')
            ->where($condition)
            ->value($field);
    }
}
