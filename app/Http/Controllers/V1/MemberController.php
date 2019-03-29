<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController as Base;
use App\Http\Controllers\BaseController;
use App\model\V1\Member;
use App\model\V1\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MemberController extends Base
{
    function  login(Request $request)
    {
        $member_name=$request->input('member_name');
        $member_passwd=$request->input('member_passwd');
        if (empty($member_name) || empty($member_passwd))
        {
           return Base::jsonReturn(1000,'参数缺失');
        }
        $storeInfo=Store::getStoreInfo(['member_name'=>$member_name]);
        if($storeInfo)
        {
            $memberInfo=Member::getMemberInfo(['member_name'=>$member_name]);
            if(md5($member_passwd)==$memberInfo->member_passwd)
            {
                $field= ['a.store_id','a.store_name','a.store_phone','a.store_avatar',
                    'a.area_info','a.store_address','a.work_start_time','a.work_end_time',
                    'a.store_state','a.store_description','a.work_start_time','a.work_end_time',
                    'b.business_licence_number_electronic',
                    'c.member_id','c.member_name','c.member_mobile'];
                $data=Store::getStoreAndJoinInfo(['a.member_id'=>$memberInfo->member_id],$field);
                if(!empty($data->store_avatar))
               {
                    $data['store_avatar']=config('data_host').'upload/shop/store/'.$data->store_avatar;
                }
                $data->business_licence_number_electronic=config('data_host').'upload/shop/store_joinin/'.$data->business_licence_number_electronic;
                $data->token=Base::makeToken($member_name,$member_passwd);
                $token_data=array(
                    'member_id'=>$memberInfo->member_id,
                    'token'=>$data->token,
                    'add_time'=>time(),
                    'expire_time'=>time()+24*5*3600
                );
                Base::makeToken($member_name,$member_passwd);
                return Base::jsonReturn(200,'获取成功',$data);
            }else{
                return Base::jsonReturn(1001,'账号或密码错误');
           }
        }else{
            return Base::jsonReturn(1003,'你还不是商家');
        }
    }
}
