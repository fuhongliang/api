<?php

namespace App\Http\Controllers\U2;


use App\BModel;
use App\Http\Controllers\BaseController as Base;
use App\model\V3\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MemberController extends Base
{


    /**验证码登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function smsLogin(Request $request)
    {
        $phone_number = $request->input('phone_number');
        $verify_code = $request->input('code');
        $device_tokens = $request->input('device_tokens');
        $app_type = $request->input('app_type');
        if (empty($phone_number) || empty($verify_code) || empty($device_tokens) || empty($app_type)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!preg_match("/^1[34578]{1}\d{9}$/", $phone_number)) {
            return Base::jsonReturn(1000, '手机号格式不正确');
        }
        $code = Redis::get($phone_number);
        if ($code !== $verify_code) {
            return Base::jsonReturn(2002, '验证码错误');
        }
        $need_pwd = false;
        if (BModel::getCount('member', ['member_mobile' => $phone_number]) == 1) {
            $member_data = BModel::getTableFirstData('member', ['member_mobile' => $phone_number], ['member_passwd', 'member_id']);
            $member_id = $member_data->member_id;
            if (!$member_data->member_passwd) {
                $need_pwd = true;
            }
            $up_data = array(
                'member_login_time' => time(),
                'member_login_ip' => $request->getClientIp()
            );
            BModel::upTableData('member', ['member_id' => $member_id], $up_data);
        } else {
            //未注册
            $ins_data = array(
                'member_mobile' => $phone_number,
                'member_name' => $phone_number,
                'member_mobile_bind' => 1,
                'member_time' => time()
            );
            $need_pwd = true;
            $member_id = BModel::insertData('member', $ins_data);
            BModel::insertData('member_common', ['member_id' => $member_id]);
        }
        DB::transaction(function () use ($member_id, $app_type, $device_tokens) {
            BModel::delData('umeng', ['member_id' => $member_id]);
            BModel::insertData('umeng', ['app_type' => $app_type, 'device_tokens' => $device_tokens, 'member_id' => $member_id]);
        });
        BModel::delData('token', ['member_id' => $member_id]);
        $member_info = BModel::getTableFieldFirstData('member', ['member_id' => $member_id], ['member_id', 'member_mobile', 'member_name', 'member_avatar']);
        $member_info->member_avatar = is_null($member_info->member_avatar) ? '' : $member_info->member_avatar;
        $member_info->need_pwd = $need_pwd;
        $member_info->token = Base::makeToken(microtime());
        $token_data = array(
            'member_id' => $member_id,
            'token' => $member_info->token,
            'add_time' => time(),
            'expire_time' => time() + 24 * 5 * 3600
        );
        Token::addToken($token_data);
        return Base::jsonReturn(200, '登录成功', $member_info);
    }

    /**账号密码登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function userLogin(Request $request)
    {
        $phone_number = $request->input('phone_number');
        $password = $request->input('password');
        $device_tokens = $request->input('device_tokens');
        $app_type = $request->input('app_type');
        if (empty($phone_number) || empty($password) || empty($device_tokens) || empty($app_type)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!preg_match("/^1[34578]{1}\d{9}$/", $phone_number)) {
            return Base::jsonReturn(1000, '手机号格式不正确');
        }
        if (BModel::getCount('member', ['member_mobile' => $phone_number]) == 1) {
            $member_data = BModel::getTableFirstData('member', ['member_mobile' => $phone_number], ['member_passwd', 'member_id']);
            if (md5($password) != $member_data->member_passwd) {
                return Base::jsonReturn(1001, '账号或密码错误');
            }
            BModel::delData('umeng', ['member_id' => $member_data->member_id]);
            BModel::insertData('umeng', ['app_type' => $app_type, 'device_tokens' => $device_tokens, 'member_id' => $member_data->member_id]);
            $up_data = array(
                'member_login_time' => time(),
                'member_login_ip' => $request->getClientIp()
            );
            BModel::upTableData('member', ['member_id' => $member_data->member_id], $up_data);
            BModel::delData('token', ['member_id' => $member_data->member_id]);
            $member_info = BModel::getTableFieldFirstData('member', ['member_id' => $member_data->member_id], ['member_id', 'member_mobile', 'member_name', 'member_avatar']);
            $member_info->member_avatar = is_null($member_info->member_avatar) ? '' : $member_info->member_avatar;
            $member_info->token = Base::makeToken(microtime());
            $token_data = array(
                'member_id' => $member_data->member_id,
                'token' => $member_info->token,
                'add_time' => time(),
                'expire_time' => time() + 24 * 5 * 3600
            );
            Token::addToken($token_data);
            return Base::jsonReturn(200, '登录成功', $member_info);
        } else {
            return Base::jsonReturn(1002, '用户不存在');
        }
    }

    /**添加密码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function userAddPwd(Request $request)
    {
        $member_id = $request->input('member_id');
        $password = $request->input('password');
        if (empty($member_id) || empty($password)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (BModel::getCount('member', ['member_id' => $member_id]) == 0) {
            return Base::jsonReturn(1001, '用户不存在');
        }
        $res = BModel::upTableData('member', ['member_id' => $member_id], ['member_passwd' => md5($password)]);
        if ($res) {
            return Base::jsonReturn(200, '添加成功');
        } else {
            return Base::jsonReturn(1002, '添加失败');
        }
    }


}
