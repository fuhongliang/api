<?php
namespace app\v1\controller;
use app\v1\controller\Base;
use app\v1\model\Member as MemberModel;
use app\v1\model\Seller as SellerModel;
use app\v1\model\Store  as StoreModel;
use think\Request;
/**
 * Class Member  商家（卖家）
 * @package app\v1\controller
 */
class Member extends Base
{
    /**
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login(Request $request)
    {
        $member_name=$request->param('member_name');
        $member_passwd=$request->param('member_passwd');
        if (empty($member_name) || empty($member_passwd))
        {
            return Base::jsonReturn(1000,[],'参数缺失');
        }
        $memberInfo=MemberModel::getMemberInfo(['member_name'=>$member_name]);
        if($memberInfo)
        {
            if(md5($member_passwd)==$memberInfo['member_passwd'])
            {
                $field= 'a.store_id,a.store_name,a.store_avatar,a.area_info,a.store_address,a.store_workingtime,a.store_phone
                ,a.store_state,a.store_description,b.business_licence_number_electronic';
                $data=StoreModel::getStoreAndJoinInfo($field);
                $data['token']=Base::makeToken($member_name,$member_passwd);
                return Base::jsonReturn(200,$data,'获取成功');
            }else{
                return Base::jsonReturn(1001,[],'账号或密码错误');
            }
        }else{
            return Base::jsonReturn(1003,[],'你还不是商家');
        }



        }

    }
