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
                $field= 'a.store_name,a.store_avatar,a.area_info,a.store_address,a.store_workingtime,a.store_phone
                ,a.store_state,a.store_description,b.business_licence_number_electronic';
                $data=StoreModel::getStoreAndJoinInfo($field);
                return Base::jsonReturn(200,$data,'获取成功');
            }else{
                return Base::jsonReturn(1001,[],'账号或密码错误');
            }
        }else{
            return Base::jsonReturn(1003,[],'你还不是商家');
        }




       // return Base::jsonReturn();
//        $seller_info = $model_seller->getSellerInfo(array('seller_name' => $seller_name));
//        if($seller_info) {
//            $model_member = Model('member');
//            $member_info = $model_member->getMemberInfo(array('member_id' => $seller_info['member_id']));
//            if($member_info) {
//                $post_password = empty($_POST['password']) ? '' : $_POST['password'];
//                $password = $member_info['member_passwd'];
//                if($password != md5($post_password)) {
//                    showDialog('密码错误','','error');
//                }
//
//                $model_seller->editSeller(array('last_login_time' => TIMESTAMP), array('seller_id' => $seller_info['seller_id']));
//                $model_seller_group = Model('seller_group');
//                $seller_group_info = $model_seller_group->getSellerGroupInfo(array('group_id' => $seller_info['seller_group_id']));
//                $model_store = Model('store');
//                $store_info = $model_store->getStoreInfoByID($seller_info['store_id']);
//
//                $_SESSION['is_login'] = '1';
//                $_SESSION['member_id'] = $member_info['member_id'];
//                $_SESSION['member_name'] = $member_info['member_name'];
//                $_SESSION['member_email'] = $member_info['member_email'];
//                $_SESSION['is_buy']	= $member_info['is_buy'];
//                $_SESSION['avatar']	= $member_info['member_avatar'];
//
//                $_SESSION['grade_id'] = $store_info['grade_id'];
//                $_SESSION['seller_id'] = $seller_info['seller_id'];
//                $_SESSION['seller_name'] = $seller_info['seller_name'];
//                $_SESSION['seller_is_admin'] = intval($seller_info['is_admin']);
//                $_SESSION['store_id'] = intval($seller_info['store_id']);
//                $_SESSION['store_name']	= $store_info['store_name'];
//                $_SESSION['is_own_shop'] = (bool) $store_info['is_own_shop'];
//                $_SESSION['bind_all_gc'] = (bool) $store_info['bind_all_gc'];
//                $_SESSION['seller_limits'] = explode(',', $seller_group_info['limits']);
//                if($seller_info['is_admin']) {
//                    $_SESSION['seller_group_name'] = '管理员';
//                    $_SESSION['seller_smt_limits'] = false;
//                } else {
//                    $_SESSION['seller_group_name'] = $seller_group_info['group_name'];
//                    $_SESSION['seller_smt_limits'] = explode(',', $seller_group_info['smt_limits']);
//                }
//                if(!$seller_info['last_login_time']) {
//                    $seller_info['last_login_time'] = TIMESTAMP;
//                }
//                $_SESSION['seller_last_login_time'] = date('Y-m-d H:i', $seller_info['last_login_time']);
//                $seller_menu = $this->getSellerMenuList($seller_info['is_admin'], explode(',', $seller_group_info['limits']));
//                $_SESSION['seller_menu'] = $seller_menu['seller_menu'];
//                $_SESSION['seller_function_list'] = $seller_menu['seller_function_list'];
//                if(!empty($seller_info['seller_quicklink'])) {
//                    $quicklink_array = explode(',', $seller_info['seller_quicklink']);
//                    foreach ($quicklink_array as $value) {
//                        $_SESSION['seller_quicklink'][$value] = $value ;
//                    }
//                }
//                $this->recordSellerLog('登录成功');
//                redirect('index.php?act=seller_center');
//            } else {
//                showMessage('用户密码错误', '', '', 'error');
//            }

        }

    }
