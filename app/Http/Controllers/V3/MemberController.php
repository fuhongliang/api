<?php

namespace App\Http\Controllers\V3;

use App\BModel;
use App\Http\Controllers\BaseController as Base;
use App\Http\Controllers\BaseController;
use App\model\V3\Member;
use App\model\V3\Store;
use App\model\V3\Token;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

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
//        if (!Member::checkStorePhoneExist(['store_phone' => $phone_number]) || !Member::checkStoreJoinPhoneExist(['contacts_phone' => $phone_number]) || !Member::checkStoreRegTmpExist(['mobile_phone' => $phone_number])) {
//            return Base::jsonReturn(1001, '手机号已存在申请记录');
//        }
        return Base::jsonReturn(200, '可以注册');
    }

    /** 商户注册
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function memberRegister(Request $request)
    {
        $phone_number  = $request->input('mobile');
        $password      = $request->input('password');
        $verify_code   = $request->input('verify_code');
        $app_type      = $request->input('app_type');
        $device_tokens = $request->input('device_tokens');
        if (empty($phone_number) || empty($password) || empty($verify_code) || empty($app_type) || empty($device_tokens)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!preg_match("/^1[34578]{1}\d{9}$/", $phone_number)) {
            return Base::jsonReturn(2000, '手机号格式不正确');
        }
        if (BModel::getCount('member', ['member_mobile' => $phone_number]) > 0) {
            return Base::jsonReturn(2001, '手机号已被占用');
        }
        $code = Redis::get($phone_number);

        if (!$verify_code || $code !== $verify_code) {
            return Base::jsonReturn(2002, '验证码错误');
        } else {
            $member_data = array(
                'member_name' => '未设置',
                'member_passwd' => md5($password),
                'member_email' => '',
                'member_mobile' => $phone_number,
                'member_time' => time(),
                'member_login_time' => time(),
                'member_old_login_time' => time()
            );
            $member_id   = Member::MemberRegister($member_data);
//            $regtmp_data = array(
//                'member_id' => $member_id,
//                'mobile_phone' => $phone_number,
//                'password' => md5($password),
//                'add_time' => time()
//            );
//            $res         = Member::insertMemberRegTmpData($regtmp_data);
            $um_data = array(
                'app_type' => $app_type,
                'device_tokens' => $device_tokens,
                'member_id' => $member_id
            );
            BModel::insertData('umeng', $um_data);
            if ($member_id) {
                $joinin_url = "";
                if (BModel::getCount('store_joinin', ['member_id' => $member_id]) == 0) {
                    //从来没申请过，开始入住
                    $joinin_url = "http://47.111.27.189:2000/#/" . $member_id;
                } else {
                    $joinin_state = BModel::getTableValue('store_joinin', ['member_id' => $member_id], 'joinin_state');
                    if ($joinin_state == 10) {
                        $joinin_url = "http://47.111.27.189:2000/#/checks/" . $member_id;
                        //已经提交申请，待审核
                    } elseif ($joinin_state == 20) {
                        $joinin_url = "http://47.111.27.189:2000/#/application/" . $member_id;
                    } elseif ($joinin_state == 30) {
                        $joinin_url = "http://47.111.27.189:2000/#/checkf/" . $member_id;
                    } elseif ($joinin_state == 11) {
                        //第二部已提交，待审核页面
                        $joinin_url = "http://47.111.27.189:2000/#/pwait/" . $member_id;
                    } elseif ($joinin_state == 31) {
                        $joinin_url = " http://47.111.27.189:2000/#/pfailed/" . $member_id;
                        //缴费审核失败页面
                    }
                }
                $res_data = array(
                    'member_id' => $member_id,
                    'joinin_url' => $joinin_url
                );
                return Base::jsonReturn(200, '注册成功', $res_data);
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
        $app_type      = $request->input('app_type');
        $device_tokens = $request->input('device_tokens');
        if (empty($member_name) || empty($member_passwd)|| empty($app_type) || empty($device_tokens)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $memberInfo = BModel::getTableFirstData('member', ['member_mobile' => $member_name]);
        if ($memberInfo) {
            $member_id = $memberInfo->member_id;
            if (md5($member_passwd) == $memberInfo->member_passwd) {
                $joinin_url = "";
                $um_data = array(
                    'app_type' => $app_type,
                    'device_tokens' => $device_tokens,
                    'member_id' => $member_id
                );
                BModel::insertData('umeng', $um_data);
                if (BModel::getCount('store_joinin', ['member_id' => $member_id]) == 0) {
                    //从来没申请过，开始入住
                    $joinin_url = "http://47.111.27.189:2000/#/" . $member_id;
                } else {
                    $joinin_state = BModel::getTableValue('store_joinin', ['member_id' => $member_id], 'joinin_state');
                    if ($joinin_state == 10) {
                        $joinin_url = "http://47.111.27.189:2000/#/checks/" . $member_id;
                        //已经提交申请，待审核
                    } elseif ($joinin_state == 20) {
                        $joinin_url = "http://47.111.27.189:2000/#/application/" . $member_id;
                    } elseif ($joinin_state == 30) {
                        $joinin_url = "http://47.111.27.189:2000/#/checkf/" . $member_id;
                    } elseif ($joinin_state == 11) {
                        //第二部已提交，待审核页面
                        $joinin_url = "http://47.111.27.189:2000/#/pwait/" . $member_id;
                    } elseif ($joinin_state == 31) {
                        $joinin_url = " http://47.111.27.189:2000/#/pfailed/" . $member_id;
                        //缴费审核失败页面
                    } elseif ($joinin_state == 40) {
                        $field = ['a.store_id', 'a.store_name', 'a.store_phone', 'a.store_avatar',
                            'a.area_info', 'a.store_address', 'a.work_start_time', 'a.work_end_time',
                            'a.store_state', 'a.store_description', 'a.work_start_time', 'a.work_end_time',
                            'b.business_licence_number_electronic',
                            'c.member_id', 'c.member_name', 'c.member_mobile'];
                        $data  = Store::getStoreAndJoinInfo(['a.member_id' => $member_id], $field);

                        //$data                                     = BModel::getTableFieldFirstData('member', ['member_id' => $member_id],['member_id','member_name']);
                        $data->token                              = Base::makeToken($member_name);
                        $token_data                               = array(
                            'member_id' => $member_id,
                            'token' => $data->token,
                            'add_time' => time(),
                            'expire_time' => time() + 24 * 5 * 3600
                        );
                        Token::addToken($token_data);
                        $old_token = Redis::get($member_id);
                        if ($old_token) {
                            BModel::delData('token', ['token' => $old_token]);
                        }
                        Redis::setex($data->member_id, 60 * 60 * 24 * 7, $data->token);
                        $data->joinin_url = '';
                        return Base::jsonReturn(200, '获取成功', $data);
                    }
                }

                return Base::jsonReturn(200, '获取成功', ['joinin_url' => $joinin_url]);

            } else {
                return Base::jsonReturn(1001, '账号或密码错误');
            }
        } else {
            return Base::jsonReturn(1003, '用户不存在');
        }
    }
}
