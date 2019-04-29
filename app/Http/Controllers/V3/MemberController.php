<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\BaseController as Base;
use App\Http\Controllers\BaseController;
use App\model\V3\Member;
use App\model\V3\Store;
use App\model\V3\Token;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MemberController extends Base
{
    /** 手机号重复监测
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function checkMobile(Request $request)
    {
        $phone_number = $request->input('mobile');
        if (empty($phone_number)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!preg_match("/^1[34578]{1}\d{9}$/", $phone_number)) {
            return Base::jsonReturn(1000, '手机号格式不正确');
        }
        if (!Member::checkStorePhoneExist(['store_phone' => $phone_number]) || !Member::checkStoreJoinPhoneExist(['contacts_phone' => $phone_number]) || !Member::checkStoreRegTmpExist(['mobile_phone' => $phone_number])) {
            return Base::jsonReturn(1001, '手机号已存在申请记录');
        }
        return Base::jsonReturn(200, '可以注册');
    }

    /** 商户注册
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function memberRegister(Request $request)
    {
        $phone_number = $request->input('mobile');
        $password     = $request->input('password');
        $verify_code  = $request->input('verify_code');
        if (empty($phone_number)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!preg_match("/^1[34578]{1}\d{9}$/", $phone_number)) {
            return Base::jsonReturn(2000, '手机号格式不正确');
        }
        if (!Member::checkStorePhoneExist(['store_phone' => $phone_number]) || !Member::checkStoreJoinPhoneExist(['contacts_phone' => $phone_number]) || !Member::checkStoreRegTmpExist(['mobile_phone' => $phone_number])) {
            return Base::jsonReturn(2001, '手机号已存在申请记录');
        }
        $code = Cache::get($phone_number);
        if (!$verify_code || $code !== $verify_code) {
            return Base::jsonReturn(2002, '验证码错误');
        } else {
            $member_data = array(
                'member_name' => '未设置',
                'member_passwd' => md5($password),
                'member_email' => '',
                'member_time' => time(),
                'member_login_time' => time(),
                'member_old_login_time' => time()
            );
            $member_id   = Member::MemberRegister($member_data);
            $regtmp_data = array(
                'member_id' => $member_id,
                'mobile_phone' => $phone_number,
                'password' => md5($password),
                'add_time' => time()
            );
            $res         = Member::insertMemberRegTmpData($regtmp_data);
            if ($res) {
                return Base::jsonReturn(200, '注册成功');
            } else {
                return Base::jsonReturn(2003, '注册失败');
            }
        }


    }


    /** 用户登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function login(Request $request)
    {
        $member_name   = $request->input('member_name');
        $member_passwd = $request->input('member_passwd');
        if (empty($member_name) || empty($member_passwd)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $storeInfo = Store::getStoreInfo(['member_name' => $member_name]);
        if ($storeInfo) {
            $memberInfo = Member::getMemberInfo(['member_name' => $member_name]);
            if (md5($member_passwd) == $memberInfo->member_passwd) {
                $field = ['a.store_id', 'a.store_name', 'a.store_phone', 'a.store_avatar',
                    'a.area_info', 'a.store_address', 'a.work_start_time', 'a.work_end_time',
                    'a.store_state', 'a.store_description', 'a.work_start_time', 'a.work_end_time',
                    'b.business_licence_number_electronic',
                    'c.member_id', 'c.member_name', 'c.member_mobile'];
                $data  = Store::getStoreAndJoinInfo(['a.member_id' => $memberInfo->member_id], $field);

                if (!empty($data->store_avatar)) {
                    $data['store_avatar'] = getenv('WEB_URL') . 'upload/shop/store/' . $data->store_avatar;
                }
                $data->business_licence_number_electronic = getenv('WEB_URL') . 'upload/shop/store_joinin/06075408577995264.png';
                $data->token                              = Base::makeToken($data->store_id, $member_name);
                $token_data                               = array(
                    'member_id' => $memberInfo->member_id,
                    'token' => $data->token,
                    'add_time' => time(),
                    'expire_time' => time() + 24 * 5 * 3600,
                    'store_id' => $data->store_id
                );
                Token::addToken($token_data);
                return Base::jsonReturn(200, '获取成功', $data);
            } else {
                return Base::jsonReturn(1001, '账号或密码错误');
            }
        } else {
            return Base::jsonReturn(1003, '你还不是商家');
        }
    }
}
