<?php

    namespace App\Http\Controllers\U2;


    use App\BModel;
    use App\Http\Controllers\BaseController as Base;
    use App\Http\Controllers\SMSController;
    use App\model\U2\Member;
    use App\model\V3\Store;
    use App\model\V3\Token;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Redis;

    class MemberController extends Base
    {

        /**发送短信
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        public function getSMS(Request $request)
        {
            $phone_number = $request->input('phone_number');
            if(!$phone_number) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!preg_match("/^1[3456789]{1}\d{9}$/", $phone_number)) {
                return Base::jsonReturn(1000, '手机号格式不正确');
            }
            if(Redis::get($phone_number)) {
                $code = Redis::get($phone_number);
            }
            else {
                $code = rand('1000', '9999');
            }
            $res = SMSController::sendSms($phone_number, $code);

            if($res->Code == 'OK') {
                Redis::setex($phone_number, 300, $code);
                return Base::jsonReturn(200, '发送成功');
            }
            else {
                return Base::jsonReturn(2000, '发送失败');
            }
        }

        /**首页
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function homePage(Request $request)
        {
            $keyword   = $request->input('keyword');
            $page      = $request->input('page');
            $longitude = $request->input('longitude');//经度
            $dimension = $request->input('dimension');//维度
            $type      = $request->input('type');
            if(empty($longitude) || empty($dimension)) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            $result = $banners = [];
            $banner = BModel::getOrderData('app_banner', 'sort', ['title', 'image_name', 'link_url']);
            if(!empty($banner)) {
                foreach($banner as $k => $v) {
                    $banners[$k]['title']      = $v->title;
                    $banners[$k]['image_name'] = getenv('ATTACH_BANNER').$v->image_name;
                    $banners[$k]['link_url']   = $v->link_url;
                }
            }
            $result['banner_data']    = $banners;
            $result['gcsort_data']    = Member::getParentGoodsClass();
            $result['discount_data']  = Member::getAppDiscount();
            $page                     = !$page ? 1 : $page;
            $result['storelist_data'] = Member::getStoreList($longitude, $dimension, $keyword, $page, $type);
            $result['city_list_url']  = getenv('HOST_URL')."/users/#/";
            return Base::jsonReturn(200, '获取成功', $result);
        }

        /**验证码登录
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function smsLogin(Request $request)
        {
            $phone_number  = $request->input('phone_number');
            $verify_code   = $request->input('code');
            $device_tokens = $request->input('device_tokens');
            $app_type      = $request->input('app_type');
            if(empty($phone_number) || empty($verify_code) || empty($device_tokens) || empty($app_type)) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!preg_match("/^1[3456789]{1}\d{9}$/", $phone_number)) {
                return Base::jsonReturn(1000, '手机号格式不正确');
            }
            $code = Redis::get($phone_number);
            if($code !== $verify_code) {
                return Base::jsonReturn(2002, '验证码错误');
            }
            $need_pwd = false;
            if(BModel::getCount('member', ['member_mobile' => $phone_number]) == 1) {
                $member_data = BModel::getTableFirstData('member', ['member_mobile' => $phone_number], ['member_passwd', 'member_id']);
                $member_id   = $member_data->member_id;
                if(!$member_data->member_passwd) {
                    $need_pwd = true;
                }
                $up_data = ['member_login_time' => time(), 'member_login_ip' => $request->getClientIp()];
                BModel::upTableData('member', ['member_id' => $member_id], $up_data);
            }
            else {
                //未注册
                $ins_data  = ['member_mobile' => $phone_number, 'member_name' => $phone_number, 'member_mobile_bind' => 1, 'member_time' => time()];
                $need_pwd  = true;
                $member_id = BModel::insertData('member', $ins_data);
                BModel::insertData('member_common', ['member_id' => $member_id]);
            }
            DB::transaction(function() use ($member_id, $app_type, $device_tokens){
                BModel::delData('umeng', ['member_id' => $member_id]);
                BModel::insertData('umeng', ['app_type' => $app_type, 'device_tokens' => $device_tokens, 'member_id' => $member_id]);
            });
            BModel::delData('token', ['member_id' => $member_id]);
            $member_info                 = BModel::getTableFieldFirstData('member', ['member_id' => $member_id], ['member_id', 'member_mobile', 'member_name', 'member_avatar', 'member_wxopenid']);
            $member_info->member_avatar  = is_null($member_info->member_avatar) ? '' : $member_info->member_avatar;
            $member_info->need_pwd       = $need_pwd;
            $member_info->is_bind_openid = empty($member_info->member_wxopenid) ? false : true;
            $member_info->token          = Base::makeToken(microtime());
            $token_data                  = ['member_id' => $member_id, 'token' => $member_info->token, 'add_time' => time(), 'expire_time' => time() + 24 * 5 * 3600];
            Token::addToken($token_data);
            return Base::jsonReturn(200, '登录成功', $member_info);
        }

        /**账号密码登录
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function userLogin(Request $request)
        {
            $phone_number  = $request->input('phone_number');
            $password      = $request->input('password');
            $device_tokens = $request->input('device_tokens');
            $app_type      = $request->input('app_type');
            if(empty($phone_number) || empty($password) || empty($device_tokens) || empty($app_type)) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!preg_match("/^1[3456789]{1}\d{9}$/", $phone_number)) {
                return Base::jsonReturn(1000, '手机号格式不正确');
            }
            if(BModel::getCount('member', ['member_mobile' => $phone_number]) == 1) {
                $member_data = BModel::getTableFirstData('member', ['member_mobile' => $phone_number], ['member_passwd', 'member_id']);
                if(md5($password) != $member_data->member_passwd) {
                    return Base::jsonReturn(1001, '账号或密码错误');
                }
                BModel::delData('umeng', ['member_id' => $member_data->member_id]);
                BModel::insertData('umeng', ['app_type' => $app_type, 'device_tokens' => $device_tokens, 'member_id' => $member_data->member_id]);
                $up_data = ['member_login_time' => time(), 'member_login_ip' => $request->getClientIp()];
                BModel::upTableData('member', ['member_id' => $member_data->member_id], $up_data);
                BModel::delData('token', ['member_id' => $member_data->member_id]);
                $member_info                 = BModel::getTableFieldFirstData('member', ['member_id' => $member_data->member_id], ['member_id', 'member_mobile', 'member_name', 'member_avatar', 'member_wxopenid']);
                $member_info->member_avatar  = is_null($member_info->member_avatar) ? '' : $member_info->member_avatar;
                $member_info->token          = Base::makeToken(microtime());
                $member_info->is_bind_openid = empty($member_info->member_wxopenid) ? false : true;
                $member_info->need_pwd       = false;
                unset($member_info->member_wxopenid);
                $token_data = ['member_id' => $member_data->member_id, 'token' => $member_info->token, 'add_time' => time(), 'expire_time' => time() + 24 * 5 * 3600];
                Token::addToken($token_data);
                return Base::jsonReturn(200, '登录成功', $member_info);
            }
            else {
                return Base::jsonReturn(1002, '用户不存在');
            }
        }

        /**添加密码
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function userAddPwd(Request $request)
        {
            $member_id = $request->input('member_id');
            $password  = $request->input('password');
            if(empty($member_id) || empty($password)) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('member', ['member_id' => $member_id]) == 0) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            $res = BModel::upTableData('member', ['member_id' => $member_id], ['member_passwd' => md5($password)]);
            if($res) {
                return Base::jsonReturn(200, '添加成功');
            }
            else {
                return Base::jsonReturn(1002, '添加失败');
            }
        }

        /**收货地址列表
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function userAddrList(Request $request)
        {
            $member_id = $request->input('member_id');
            if(empty($member_id)) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('member', ['member_id' => $member_id]) == 0) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            $field  = ['address_id', 'area_info', 'address', 'mob_phone', 'sex', 'true_name', 'is_default'];
            $res    = BModel::getTableAllData('address', ['member_id' => $member_id], $field);
            $result = $res->isEmpty() ? [] : $res->toArray();
            return Base::jsonReturn(200, '获取成功', $result);
        }

        /**删除地址
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function userAddrDel(Request $request)
        {
            $address_id = $request->input('address_id');
            $member_id  = $request->input('member_id');
            if(empty($address_id) || empty($member_id)) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('address', ['address_id' => $address_id]) == 0) {
                return Base::jsonReturn(1001, '地址不存在');
            }
            $res = BModel::delData('address', ['address_id' => $address_id, 'member_id' => $member_id]);
            if($res) {
                return Base::jsonReturn(200, '删除成功');
            }
            else {
                return Base::jsonReturn(2000, '删除失败');
            }

        }

        /**地址详情
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function userAddrInfo(Request $request)
        {
            $address_id = $request->input('address_id');
            if(empty($address_id)) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('address', ['address_id' => $address_id]) == 0) {
                return Base::jsonReturn(1001, '地址不存在');
            }
            $field           = ['address_id', 'area_info', 'address', 'mob_phone', 'sex', 'true_name', 'is_default'];
            $res             = BModel::getTableFieldFirstData('address', ['address_id' => $address_id], $field);
            $res->is_default = intval($res->is_default);
            return Base::jsonReturn(200, '获取成功', $res);
        }


        /**保存收货地址
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function userAddrSave(Request $request)
        {
            $address_id = $request->input('address_id');
            $member_id  = $request->input('member_id');
            $true_name  = $request->input('true_name');
            $sex        = $request->input('sex');
            $mob_phone  = $request->input('mobile_phone');
            $area_info  = $request->input('area_info');
            $address    = $request->input('address');
            $is_default = $request->input('is_default');
            if(!$member_id || !$true_name || !$sex || !$mob_phone || !$area_info || !$address) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('member', ['member_id' => $member_id]) == 0) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            if($address_id && BModel::getCount('address', ['address_id' => $address_id]) == 0) {
                return Base::jsonReturn(1002, '地址不存在');
            }
            if($is_default && $is_default == 1) {
                $addressid = BModel::getTableValue('address', ['member_id' => $member_id, 'is_default' => '1'], 'address_id');
                BModel::upTableData('address', ['address_id' => $addressid], ['is_default' => '0']);
            }
            $ins_data = ['member_id' => $member_id, 'true_name' => $true_name, 'area_info' => $area_info, 'address' => $address, 'mob_phone' => $mob_phone, 'sex' => $sex, 'is_default' => $is_default == 1 ? '1' : '0'];
            if($address_id) {
                $result = BModel::upTableData('address', ['address_id' => $address_id], $ins_data);
            }
            else {
                $result = BModel::insertData('address', $ins_data);
            }
            if($result) {
                return Base::jsonReturn(200, '操作成功');
            }
            else {
                return Base::jsonReturn(1003, '操作失败');
            }
        }


        /**
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function allComment(Request $request)
        {
            $store_id = $request->input('store_id');
            $type     = $request->input('type');//1,2,3,4
            $result   = [];

            if(!$store_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('store', ['store_id' => $store_id]) == 0) {
                return Base::jsonReturn(1001, '店铺不存在');
            }
            $result['pingfen']['peisong']   = 0;
            $result['pingfen']['baozhuang'] = 0;
            $result['pingfen']['kouwei']    = 0;
            $result['comment']['all']       = BModel::getCount('store_com', ['store_id' => $store_id]);

            $result['comment']['manyi']   = Member::getManyi($store_id);
            $result['comment']['bumanyi'] = Member::getBuManyi($store_id);
            $result['comment']['youtu']   = Member::getYoutu($store_id);
            $result['comment']['list']    = Member::getStoreComList($store_id, $type);
            return Base::jsonReturn(200, '获取成功', $result);

        }

        /**
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function storDetail(Request $request)
        {
            $store_id = $request->input('store_id');
            $result   = [];

            if(!$store_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('store', ['store_id' => $store_id]) == 0) {
                return Base::jsonReturn(1001, '店铺不存在');
            }
            $field           = ['a.area_info', 'a.store_address', 'b.face_img', 'b.logo_img', 'a.store_name', 'a.work_start_time', 'a.work_end_time', 'a.store_phone', 'a.sc_id', 'b.business_licence_number_electronic'];
            $store_info      = BModel::getLeftData('store as a', 'store_joinin as b', 'a.member_id', 'b.member_id', ['a.store_id' => $store_id], $field)->first();
            $result          = $store_info;
            $result->sc_name = BModel::getTableValue('store_class', ['sc_id' => $store_info->sc_id], 'sc_name');
            return Base::jsonReturn(200, '获取成功', $result);

        }

        /**添加购物车
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function addCart(Request $request)
        {
            $store_id  = $request->input('store_id');
            $member_id = $request->input('member_id');
            $stc_id    = $request->input('stc_id');
            $goods_id  = $request->input('goods_id');
            $quantity  = $request->input('nums');

            if(!$member_id || !$store_id || !$stc_id || !$goods_id || !$quantity) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('member', ['member_id' => $member_id]) == 0) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            $storeid = BModel::getTableValue('store', ['member_id' => $member_id], 'store_id');
            if(!$storeid) {
                $storeid = 0;
            }
            if($stc_id == 'taozhuang') {
                $bl_info = BModel::getTableFirstData('p_bundling', ['bl_id' => $goods_id]);
                if(empty($bl_info) || $bl_info->bl_state == 0) {
                    return Base::jsonReturn(1001, '该优惠套装已不存在');
                }

                //检查每个商品是否符合条件,并重新计算套装总价
                $bl_goods_list  = BModel::getTableAllData('p_bundling_goods', ['bl_id' => $goods_id]);
                $goods_id_array = [];
                $bl_amount      = 0;
                foreach($bl_goods_list as $goods) {
                    $goods_id_array[] = $goods->goods_id;
                    $bl_amount        += $goods->bl_goods_price;
                }

                $goods_list = Member::getGoodsOnlineListAndPromotionByIdArray($goods_id_array);
                foreach($goods_list as $goods_info) {
                    if(empty($goods_info)) {
                        return Base::jsonReturn(1001, '商品不存在');
                    }
                    if($goods_info->store_id == $storeid) {
                        return Base::jsonReturn(1002, '不能购买自己的商品');
                    }
                    if($goods_info->is_much == 1 && intval($goods_info->goods_storage) < 1) {
                        return Base::jsonReturn(1003, '没有库存');
                    }
                    if($goods_info->is_much == 1 && intval($goods_info->goods_storage) < $quantity) {
                        return Base::jsonReturn(1004, '库存不足');
                    }
                }

                if(BModel::getCount('cart', ['bl_id' => $goods_id, 'buyer_id' => $member_id]) > 0) {
                    DB::transaction(function() use ($goods_info, $goods_id_array, $quantity, $member_id, $goods_id, $bl_amount){
                        BModel::numIncrement('cart', ['bl_id' => $goods_id, 'buyer_id' => $member_id], 'goods_num', $quantity);
                        foreach($goods_id_array as $goodsid) {
                            $is_much = BModel::getTableValue('goods', ['goods_id' => $goodsid], 'is_much');
                            if($is_much == 1) {
                                BModel::numDecrement('goods', ['goods_id' => $goodsid], 'goods_storage', $quantity);
                            }
                        }
                    });
                }
                else {
                    //优惠套装作为一条记录插入购物车，图片取套装内的第一个商品图
                    $goods_info                = [];
                    $goods_info['store_id']    = $store_id;
                    $goods_info['goods_id']    = $goods_list[0]->goods_id;
                    $goods_info['goods_name']  = $bl_info->bl_name;
                    $goods_info['goods_price'] = Base::ncPriceFormat($bl_info->bl_discount_price);
                    $goods_info['goods_num']   = $quantity;
                    $goods_info['goods_image'] = $goods_list[0]->goods_image;
                    $goods_info['store_name']  = $bl_info->store_name;
                    $goods_info['bl_id']       = $goods_id;
                    $goods_info['buyer_id']    = $member_id;

                    DB::transaction(function() use ($goods_info, $goods_id_array, $quantity){
                        BModel::insertData('cart', $goods_info);
                        foreach($goods_id_array as $goodsid) {
                            $is_much = BModel::getTableValue('goods', ['goods_id' => $goodsid], 'is_much');
                            if($is_much == 1) {
                                BModel::numDecrement('goods', ['goods_id' => $goodsid], 'goods_storage', $quantity);
                            }
                        }
                    });
                }

            }
            else if($stc_id == 'xianshi') {
                $xianshi_info = BModel::getTableFirstData('p_xianshi', ['xianshi_id' => $goods_id]);
                if(empty($xianshi_info) || $xianshi_info->state == 0 || $xianshi_info->end_time <= time()) {
                    return Base::jsonReturn(1001, '该限时优惠已不存在');
                }
                if($quantity < $xianshi_info->lower_limit) {
                    return Base::jsonReturn(1002, '数量不能低于购买下限的数量');
                }
                //检查每个商品是否符合条件,并重新计算套装总价
                $xianshi_goods_list = BModel::getTableAllData('p_xianshi_goods', ['xianshi_id' => $goods_id]);
                $goods_id_array     = [];
                $xianshi_amount     = 0;
                foreach($xianshi_goods_list as $goods) {
                    $goods_id_array[] = $goods->goods_id;
                    $xianshi_amount   += $goods->xianshi_price;
                }
                $goods_list = Member::getGoodsOnlineListAndPromotionByIdArray($goods_id_array);
                foreach($goods_list as $goods_info) {
                    if(empty($goods_info)) {
                        return Base::jsonReturn(1001, '商品不存在');
                    }
                    if($goods_info->store_id == $storeid) {
                        return Base::jsonReturn(1002, '不能购买自己的商品');
                    }
                    if($goods_info->is_much == 1 && intval($goods_info->goods_storage) < 1) {
                        return Base::jsonReturn(1003, '没有库存');
                    }
                    if($goods_info->is_much == 1 && intval($goods_info->goods_storage) < $quantity) {
                        return Base::jsonReturn(1004, '库存不足');
                    }
                }
                if(BModel::getCount('cart', ['xs_id' => $goods_id, 'buyer_id' => $member_id]) > 0) {
                    DB::transaction(function() use ($goods_info, $goods_id_array, $quantity, $member_id, $goods_id, $xianshi_amount){
                        BModel::numIncrement('cart', ['xs_id' => $goods_id, 'buyer_id' => $member_id], 'goods_num', $quantity);
                        foreach($goods_id_array as $goodsid) {
                            $is_much = BModel::getTableValue('goods', ['goods_id' => $goodsid], 'is_much');
                            if($is_much == 1) {
                                BModel::numDecrement('goods', ['goods_id' => $goodsid], 'goods_storage', $quantity);
                            }
                        }
                    });
                }
                else {
                    $goods_info                = [];
                    $goods_info['store_id']    = $store_id;
                    $goods_info['goods_id']    = $goods_list[0]->goods_id;
                    $goods_info['goods_name']  = $xianshi_info->xianshi_name;
                    $goods_info['goods_price'] = Base::ncPriceFormat($xianshi_amount);
                    $goods_info['goods_num']   = $quantity;
                    $goods_info['goods_image'] = $goods_list[0]->goods_image;
                    $goods_info['store_name']  = $xianshi_info->store_name;
                    $goods_info['buyer_id']    = $member_id;
                    $goods_info['xs_id']       = $goods_id;

                    DB::transaction(function() use ($goods_info, $goods_id_array, $quantity){
                        BModel::insertData('cart', $goods_info);
                        foreach($goods_id_array as $goodsid) {
                            $is_much = BModel::getTableValue('goods', ['goods_id' => $goodsid], 'is_much');
                            if($is_much == 1) {
                                BModel::numDecrement('goods', ['goods_id' => $goodsid], 'goods_storage', $quantity);
                            }
                        }
                    });
                }

            }
            else if($stc_id == 'hot') {
                $goods_info = BModel::getTableFirstData('goods', ['goods_id' => $goods_id]);
                if(empty($goods_info) || $goods_info->goods_state != 1) {
                    return Base::jsonReturn(1001, '商品不存在或已下架');
                }
                if($goods_info->is_much == 1 && intval($goods_info->goods_storage) < $quantity) {
                    return Base::jsonReturn(1002, '商品库存不足');
                }
                //不能购买自己店铺
                if($goods_info->store_id == $storeid) {
                    return Base::jsonReturn(1003, '不能购买自己的商品');
                }

                if(BModel::getCount('cart', ['goods_id' => $goods_id, 'buyer_id' => $member_id, 'bl_id' => 0, 'xs_id' => 0]) > 0) {
                    DB::transaction(function() use ($quantity, $member_id, $goods_id, $goods_info){
                        BModel::numIncrement('cart', ['goods_id' => $goods_id, 'buyer_id' => $member_id], 'goods_num', $quantity);
                        $is_much = BModel::getTableValue('goods', ['goods_id' => $goods_id], 'is_much');
                        if($is_much == 1) {
                            BModel::numDecrement('goods', ['goods_id' => $goods_id], 'goods_storage', $quantity);
                        }
                    });
                }
                else {
                    $goodsinfo                = [];
                    $goodsinfo['store_id']    = $store_id;
                    $goodsinfo['goods_id']    = $goods_id;
                    $goodsinfo['goods_name']  = $goods_info->goods_name;
                    $goodsinfo['goods_price'] = Base::ncPriceFormat($goods_info->goods_price);
                    $goodsinfo['goods_num']   = $quantity;
                    $goodsinfo['goods_image'] = $goods_info->goods_image;
                    $goodsinfo['store_name']  = $goods_info->store_name;
                    $goodsinfo['buyer_id']    = $member_id;
                    $goodsinfo['hot']         = 1;
                    DB::transaction(function() use ($goodsinfo, $goods_id, $quantity){
                        BModel::insertData('cart', $goodsinfo);
                        $is_much = BModel::getTableValue('goods', ['goods_id' => $goods_id], 'is_much');
                        if($is_much == 1) {
                            BModel::numDecrement('goods', ['goods_id' => $goods_id], 'goods_storage', $quantity);
                        }
                    });
                }
            }
            else {
                $goods_info = BModel::getTableFirstData('goods', ['goods_id' => $goods_id]);
                if(empty($goods_info) || $goods_info->goods_state != 1) {
                    return Base::jsonReturn(1001, '商品不存在或已下架');
                }
                if($goods_info->is_much == 1 && intval($goods_info->goods_storage) < $quantity) {
                    return Base::jsonReturn(1002, '商品库存不足');
                }
                //不能购买自己店铺
                if($goods_info->store_id == $storeid) {
                    return Base::jsonReturn(1003, '不能购买自己的商品');
                }

                if(BModel::getCount('cart', ['goods_id' => $goods_id, 'buyer_id' => $member_id, 'bl_id' => 0, 'xs_id' => 0]) > 0) {
                    DB::transaction(function() use ($quantity, $member_id, $goods_id, $goods_info){
                        BModel::numIncrement('cart', ['goods_id' => $goods_id, 'buyer_id' => $member_id], 'goods_num', $quantity);
                        $is_much = BModel::getTableValue('goods', ['goods_id' => $goods_id], 'is_much');
                        if($is_much == 1) {
                            BModel::numDecrement('goods', ['goods_id' => $goods_id], 'goods_storage', $quantity);
                        }
                    });
                }
                else {
                    $goodsinfo                = [];
                    $goodsinfo['store_id']    = $store_id;
                    $goodsinfo['goods_id']    = $goods_id;
                    $goodsinfo['goods_name']  = $goods_info->goods_name;
                    $goodsinfo['goods_price'] = Base::ncPriceFormat($goods_info->goods_price);
                    $goodsinfo['goods_num']   = $quantity;
                    $goodsinfo['goods_image'] = $goods_info->goods_image;
                    $goodsinfo['store_name']  = $goods_info->store_name;
                    $goodsinfo['buyer_id']    = $member_id;
                    DB::transaction(function() use ($goodsinfo, $goods_id, $quantity){
                        BModel::insertData('cart', $goodsinfo);
                        $is_much = BModel::getTableValue('goods', ['goods_id' => $goods_id], 'is_much');
                        if($is_much == 1) {
                            BModel::numDecrement('goods', ['goods_id' => $goods_id], 'goods_storage', $quantity);
                        }
                    });
                }
            }
            $data         = [];
            $data['nums'] = BModel::getCount('cart', ['store_id' => $store_id, 'buyer_id' => $member_id]);
            $carts        = BModel::getTableAllData('cart', ['store_id' => $store_id, 'buyer_id' => $member_id], ['goods_price', 'goods_num']);
            $money        = 0;
            foreach($carts as $cart) {
                $money += $cart->goods_price * $cart->goods_num;
            }
            $data['amount'] = Base::ncPriceFormat($money);
            return Base::jsonReturn(200, '获取成功', $data);
        }

        /**店铺代金券
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function voucherList(Request $request)
        {
            $store_id  = $request->input('store_id');
            $member_id = $request->input('member_id');
            if(!$store_id || !$member_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            $result           = [];
            $voucher_template = BModel::getTableAllData('voucher_template', ['voucher_t_store_id' => $store_id, 'voucher_t_state' => 1]);
            if(!$voucher_template->isEmpty()) {
                foreach($voucher_template as $k => $v) {
                    $result[$k]['voucher_t_id']        = $v->voucher_t_id;
                    $result[$k]['voucher_t_title']     = $v->voucher_t_title;
                    $result[$k]['voucher_t_eachlimit'] = $v->voucher_t_eachlimit;
                    $result[$k]['voucher_t_end_date']  = date('Y-m-d', $v->voucher_t_end_date);
                    $result[$k]['voucher_t_price']     = $v->voucher_t_price;
                    $count                             = BModel::getCount('voucher', ['voucher_t_id' => $v->voucher_t_title, 'voucher_store_id' => $v->voucher_t_store_id, 'voucher_owner_id' => $member_id]);
                    $result[$k]['is_owner']            = $count > 0 ? true : false;
                }
            }
            return Base::jsonReturn(200, '获取成功', $result);
        }

        /**领取代金券
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function getVoucher(Request $request)
        {
            $voucher_t_id = $request->input('voucher_t_id');
            $member_id    = $request->input('member_id');
            if(!$voucher_t_id || !$member_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('member', ['member_id' => $member_id]) == 0) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            $voucher_info = BModel::getTableFirstData('voucher_template', ['voucher_t_id' => $voucher_t_id]);
            if(!$voucher_info) {
                return Base::jsonReturn(1001, '代金券不存在');
            }
            $store_id = BModel::getTableValue('store', ['member_id' => $member_id], 'store_id');
            if($store_id && $store_id == $voucher_info->voucher_t_store_id) {
                return Base::jsonReturn(1002, '不能领取自己店铺代金券');
            }
            $member_name = BModel::getTableValue('member', ['member_id' => $member_id], 'member_name');
            $ins_data    = ['voucher_code' => $voucher_info->voucher_t_id, 'voucher_t_id' => $voucher_info->voucher_t_id, 'voucher_title' => $voucher_info->voucher_t_title, 'voucher_desc' => $voucher_info->voucher_t_desc, 'voucher_start_date' => $voucher_info->voucher_t_start_date, 'voucher_end_date' => $voucher_info->voucher_t_end_date, 'voucher_price' => $voucher_info->voucher_t_price, 'voucher_limit' => $voucher_info->voucher_t_limit, 'voucher_store_id' => $voucher_info->voucher_t_store_id, 'voucher_state' => 1, 'voucher_active_date' => $voucher_info->voucher_t_add_date, 'voucher_type' => 1, 'voucher_owner_id' => $member_id, 'voucher_owner_name' => $member_name];
            $res         = BModel::insertData('voucher', $ins_data);
            if($res) {
                return Base::jsonReturn(200, '领取成功');
            }
            else {
                return Base::jsonReturn(2000, '领取失败');
            }
        }

        /**商品详情
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function goodsDetail(Request $request)
        {
            $store_id  = $request->input('store_id');
            $member_id = $request->input('member_id');///////////////////////////////
            $goods_id  = $request->input('goods_id');
            if(!$store_id || !$goods_id || !$member_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('goods', ['goods_id' => $goods_id]) == 0) {
                return Base::jsonReturn(1001, '商品不存在');
            }
            $data                    = [];
            $goods_field             = ['a.goods_id', 'a.goods_image', 'a.goods_name', 'a.goods_salenum', 'a.goods_price', 'a.goods_marketprice', 'b.goods_body as describe'];
            $data['goods_info']      = BModel::getLeftData('goods as a', 'goods_common as b', 'a.goods_commonid', 'b.goods_commonid', ['a.goods_id' => $goods_id], $goods_field)->first();
            $data['goods_info']->zan = BModel::getCount('goods_zan', ['goods_id' => $goods_id]);
            $com_field               = ['b.member_name', 'b.member_avatar', 'a.geval_content', 'a.geval_addtime'];
            $data['com_info']        = Member::getGoodsComData(['geval_goodsid' => $goods_id], $member_id, $goods_id, $com_field);
            if($member_id) {
                $count              = BModel::getCount('favorites', ['member_id' => $member_id, 'fav_type' => 'store', 'store_id' => $store_id]);
                $data['is_collect'] = $count == 1 ? true : false;
            }
            else {
                $data['is_collect'] = false;
            }

            $data['cart']['nums'] = BModel::getCount('cart', ['store_id' => $store_id, 'buyer_id' => $member_id]);
            $carts                = BModel::getTableAllData('cart', ['store_id' => $store_id, 'buyer_id' => $member_id], ['goods_price', 'goods_num']);
            $money                = 0;
            foreach($carts as $cart) {
                $money += $cart->goods_price * $cart->goods_num;
            }
            $data['cart']['amount'] = Base::ncPriceFormat($money);
            return Base::jsonReturn(200, '请求成功', $data);
        }

        /**评论页面
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function storeComInfo(Request $request)
        {
            $order_id  = $request->input('order_id');
            $member_id = $request->input('member_id');
            if(BModel::getCount('order', ['order_id' => $order_id, 'buyer_id' => $member_id]) == 0) {
                return Base::jsonReturn(1001, '订单不存在');
            }
            $result           = [];
            $result['qishou'] = ['member_name' => '张琪', 'avator' => "xxxxx.jpg", 'time' => "2019-01-02 12:01"];
            $store_info       = DB::table('store as a')->leftJoin('order_goods as b', 'a.store_id', 'b.store_id')->where(['b.order_id' => $order_id])->get(['a.store_id', 'a.store_name', 'a.store_avatar', 'b.goods_id', 'b.goods_name'])->toArray();
            $array            = [];
            foreach($store_info as $k => $v) {
                $array[$k]['goods_id']   = $v->goods_id;
                $array[$k]['goods_name'] = $v->goods_name;
                $store_name              = $v->store_name;
                $store_avatar            = $v->store_avatar;
                $store_id                = $v->store_id;
            }
            $result['info'] = ['goods_info' => $array, 'store_name' => is_null($store_name) ? "" : $store_name, 'store_avatar' => is_null($store_avatar) ? "" : $store_avatar, 'store_id' => $store_id];
            return Base::jsonReturn(200, '请求成功', $result);
        }

        /**评论店铺
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function storeCom(Request $request)
        {
            $store_id     = $request->input('store_id');
            $member_id    = $request->input('member_id');
            $content      = $request->input('content');
            $kouwei       = $request->input('kouwei', 0);
            $baozhuang    = $request->input('baozhuang', 0);
            $images       = $request->input('images');
            $zan_goods_id = $request->input('zan_goods_id');
            $images       = !$images ? "" : implode(',', $images);
            if(!$store_id || !$content || !$member_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('store', ['store_id' => $store_id]) == 0) {
                return Base::jsonReturn(1001, '店铺不存在');
            }
            $ins_data = ['content' => $content, 'member_id' => $member_id, 'kouwei' => $kouwei, 'baozhuang' => $baozhuang, 'add_time' => time(), 'store_id' => $store_id, 'images' => $images];
            $res      = BModel::insertData('store_com', $ins_data);
            if(!empty($zan_goods_id)) {
                foreach($zan_goods_id as $id) {
                    BModel::insertData('goods_zan', ['goods_id' => $id, 'member_id' => $member_id]);
                }
            }
            if($res) {
                return Base::jsonReturn(200, '评价成功');
            }
            else {
                return Base::jsonReturn(2000, '评价失败');
            }
        }

        /**店铺评论列表
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function storeComList(Request $request)
        {
            $store_id = $request->input('store_id');
            $type     = $request->input('type');

            $data                         = [];
            $data['pingfen']['peisong']   = 0;
            $data['pingfen']['baozhuang'] = 0;
            $data['pingfen']['kouwei']    = 0;

            $data['com']['all']     = BModel::getCount('store_com', ['store_id' => $store_id]);
            $data['com']['manyi']   = Member::getManyi($store_id);
            $data['com']['bumanyi'] = Member::getBuManyi($store_id);
            $data['com']['youtu']   = Member::getYoutu($store_id);
            $data['com']['list']    = Member::getStoreComList($store_id, $type);
            return Base::jsonReturn(200, '获取成功', $data);
        }

        /**店铺详情
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function storeDetail(Request $request)
        {
            $store_id            = $request->input('store_id');
            $field               = ['a.area_info', 'a.store_address', 'b.face_img', 'b.logo_img', 'a.store_name', 'a.work_start_time', 'a.work_end_time', 'a.store_phone', 'a.sc_id'];
            $store_info          = BModel::getLeftData('store as a', 'store_joinin as b', 'a.member_id', 'b.member_id', ['a.store_id' => $store_id], $field)->first();
            $store_info->sc_name = BModel::getTableValue('store_class', ['sc_id' => $store_info->sc_id], 'sc_name');
            return Base::jsonReturn(200, '获取成功', $store_info);
        }

        /**获取uest $request
         *
         * @return \Illuminate\Http\JsonResponse
         */
        function myCart(Request $request)
        {
            $member_id = $request->input('member_id');
            if(!$member_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('member', ['member_id' => $member_id]) == 0) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            $cart_data = DB::table('cart')->where('buyer_id', $member_id)->distinct()->get(['store_id'])->toArray();
            $result    = $data = [];
            $amount    = 0;
            foreach($cart_data as $cart_datum) {
                $result['list'] = BModel::getTableAllData('cart', ['store_id' => $cart_datum->store_id], ['goods_id', 'goods_name', 'goods_price', 'goods_num', 'goods_image'])->toArray();
                if(!empty($result['list'])) {
                    foreach($result['list'] as $v) {
                        $amount += $v->goods_price * $v->goods_num;
                    }
                }
                $result['amount'] = Base::ncPriceFormat($amount);
                $result['store']  = BModel::getTableFieldFirstData('store', ['store_id' => $cart_datum->store_id], ['store_name', 'store_id']);
                $data[]           = $result;
            }
            return Base::jsonReturn(200, '获取成功', $data);
        }

        /**清空购物车
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function clearCart(Request $request)
        {
            $member_id = $request->input('member_id');
            if(!$member_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('member', ['member_id' => $member_id]) == 0) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            $res = BModel::delData('cart', ['buyer_id' => $member_id]);
            if($res) {
                return Base::jsonReturn(200, '清空成功');
            }
            else {
                return Base::jsonReturn(2000, '清空失败');
            }
        }

        /**确认订单
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function Settlement(Request $request)
        {
            $store_id  = $request->input('store_id');
            $member_id = $request->input('member_id');
            if(!$member_id || !$store_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('member', ['member_id' => $member_id]) == 0) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            $result            = [];
            $address           = BModel::getTableFieldFirstData('address', ['member_id' => $member_id, 'is_default' => '1'], ['true_name', 'mob_phone', 'area_info', 'address', 'address_id']);
            $result['address'] = !$address ? (object)[] : $address;

            $result['store_detail'] = BModel::getTableFieldFirstData('store', ['store_id' => $store_id], ['store_id', 'store_name']);

            $data                        = Member::getCartInfoByStoreId($store_id, $member_id);
            $result['goods_detail']      = $data['data'];
            $amount                      = $data['amount'];
            $result['peisong_amount']    = 5;
            $result['manjian_amount']    = Member::getManSongCount($store_id, $amount);
            $result['daijinquan_amount'] = Member::getVoucherCount($store_id, $member_id, $amount);
            $result['daijinquan_list']   = Member::getUserVoucherList($store_id, $member_id, $amount);
            $total                       = $amount + $result['peisong_amount'] - $result['manjian_amount'] - $result['daijinquan_amount'];
            $result['total_amount']      = $total <= 0 ? 0 : $total;
            return Base::jsonReturn(200, '获取成功', $result);
        }

        /**生成订单
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function buyStep(Request $request)
        {
            $store_id       = $request->input('store_id');
            $member_id      = $request->input('member_id');
            $address_id     = $request->input('address_id');
            $voucher_id     = $request->input('voucher_id');
            $payment_code   = $request->input('payment_code');//支付宝，微信
            $shipping_time  = $request->input('shipping_time');
            $order_message  = $request->input('order_message');
            $peisong_amount = $request->input('peisong_amount');

            if(!$member_id || !$store_id || !$address_id || !$payment_code || !$shipping_time || !$peisong_amount) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('member', ['member_id' => $member_id]) == 0) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            if(BModel::getCount('store', ['store_id' => $store_id]) == 0) {
                return Base::jsonReturn(1002, '店铺不存在');
            }
            //验证收货地址
            if($address_id <= 0) {
                return Base::jsonReturn(1003, '请选择收货地址');
            }
            else {
                $input_address_info = BModel::getTableFieldFirstData('address', ['address_id' => $address_id], ['*']);
                if($input_address_info->member_id != $member_id) {
                    return Base::jsonReturn(1004, '请选择收货地址');
                }
            }
            $data    = Member::getCartInfoByStoreId($store_id, $member_id);
            $amount  = $data['amount'];
            $manjian = Member::getManSongCount($store_id, $amount);

            $voucher_price = 0;
            if($voucher_id) {
                $voucher_price = BModel::getTableValue('voucher', ['voucher_id' => $voucher_id], 'voucher_price');
                $voucher_price = !$voucher_price ? 0 : $voucher_price;
            }
            $total                   = $amount + $peisong_amount - $manjian - $voucher_price;
            $total                   = $total <= 0 ? 0 : $total;
            $pay_sn                  = Base::makePaySn($member_id);
            $order_pay_data          = ['pay_sn' => $pay_sn, 'buyer_id' => $member_id,];
            $pay_id                  = BModel::insertData('order_pay', $order_pay_data);
            $order_data              = ['order_sn' => Base::makeOrderSn($pay_id), 'pay_sn' => $pay_sn, 'store_id' => $store_id, 'store_name' => BModel::getTableValue('store', ['store_id' => $store_id], 'store_name'), 'buyer_id' => $member_id, 'buyer_name' => BModel::getTableValue('member', ['member_id' => $member_id], 'member_name'), 'add_time' => time(), 'payment_code' => $payment_code, 'goods_amount' => $total, 'order_amount' => $total, 'buyer_email' => "xxx", 'shipping_fee' => $peisong_amount, 'order_state' => 10,];
            $order_id                = BModel::insertData('order', $order_data);
            $reciver_info['address'] = $input_address_info->area_info.'&nbsp;'.$input_address_info->address;
            $reciver_info['phone']   = $input_address_info->mob_phone.($input_address_info->tel_phone ? ','.$input_address_info->tel_phone : null);
            $reciver_info['sex']     = $input_address_info->sex;
            $reciver_info            = serialize($reciver_info);
            $order_common_data       = ['store_id' => $store_id, 'shipping_time' => strtotime($shipping_time), 'order_message' => $order_message, 'voucher_price' => $voucher_price, 'voucher_code' => $voucher_id, 'reciver_name' => $input_address_info->true_name, 'reciver_info' => $reciver_info, 'reciver_city_id' => $input_address_info->city_id,];
            BModel::insertData('order_common', $order_common_data);

            $cart_id = [];
            $data_   = $data['data'];
            foreach($data_ as $v) {
                array_push($cart_id, $v->cart_id);
            }
            $cart_id = array_unique($cart_id);
            foreach($cart_id as $v) {
                $cart_data   = BModel::getTableFirstData('cart', ['cart_id' => $v]);
                $gc_id       = BModel::getTableValue('goods', ['goods_id' => $cart_data->goods_id], 'gc_id');
                $commis_rate = BModel::getTableValue('goods_class', ['gc_id' => $gc_id], 'commis_rate');
                $commis_rate = is_null($commis_rate) ? 0 : $commis_rate;
                if($cart_data->bl_id != 0) {
                    $bl_data = BModel::getTableAllData('p_bundling_goods', ['bl_id' => $cart_data->bl_id]);
                    foreach($bl_data as $val) {
                        $order_goods = ['order_id' => $order_id, 'goods_id' => $val->goods_id, 'goods_name' => $val->goods_name, 'goods_price' => $val->goods_price, 'goods_num' => $cart_data->goods_num, 'goods_image' => $val->goods_image, 'goods_pay_price' => $val->goods_price, 'store_id' => $store_id, 'buyer_id' => $member_id, 'goods_type' => 4, 'promotions_id' => $cart_data['bl_id'], 'commis_rate' => $commis_rate, 'gc_id' => $gc_id];
                        BModel::insertData('order_goods', $order_goods);
                    }

                }
                else if($cart_data->xs_id != 0) {
                    $xs_data = BModel::getTableAllData('p_xianshi_goods', ['xianshi_id' => $cart_data->xs_id]);
                    foreach($xs_data as $val) {
                        $order_goods = ['order_id' => $order_id, 'goods_id' => $val->goods_id, 'goods_name' => $val->goods_name, 'goods_price' => $val->goods_price, 'goods_num' => $cart_data->goods_num, 'goods_image' => $val->goods_image, 'goods_pay_price' => $val->xianshi_price, 'store_id' => $store_id, 'buyer_id' => $member_id, 'goods_type' => 3, 'promotions_id' => $cart_data['xs_id'], 'commis_rate' => $commis_rate, 'gc_id' => $gc_id];
                        BModel::insertData('order_goods', $order_goods);
                    }
                }
                else {
                    $order_goods = ['order_id' => $order_id, 'goods_id' => $cart_data->goods_id, 'goods_name' => $cart_data->goods_name, 'goods_price' => $cart_data->goods_price, 'goods_num' => $cart_data->goods_num, 'goods_image' => $cart_data->goods_image, 'goods_pay_price' => $cart_data->goods_price, 'store_id' => $store_id, 'buyer_id' => $member_id, 'commis_rate' => $commis_rate, 'gc_id' => $gc_id];
                    BModel::insertData('order_goods', $order_goods);
                }
            }
            $result = $this->wxPay($total, Base::makeOrderSn($pay_id));
            return Base::jsonReturn(200, '下单成功', $result);
        }

        /**订单列表
         *
         * @param Request $request
         */
        function orderList(Request $request)
        {

            $member_id = $request->input('member_id');
            $type      = $request->input('type');//全部 2 未评价 3 退款
            if(!$member_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('member', ['member_id' => $member_id]) == 0) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            $data = [];
            if(!$type || $type == 1) {
                $data = Member::getAllOrder($member_id);
            }
            else if($type == 2) {
                $data = Member::getEvaluationOrder($member_id);
            }
            else if($type == 3) {
                $data = Member::getRefundStateOrder($member_id);
            }
            if($data) {
                $field = ['goods_id', 'goods_name', 'goods_price', 'goods_num'];
                foreach($data as $k => &$v) {
                    $amount        = 0;
                    $order_data    = DB::table('order_goods')->where('order_id', $v->order_id)->get($field)->toArray();
                    $v->goods_list = $order_data;
                    foreach($order_data as $order_datum) {
                        $amount += $order_datum->goods_price * $order_datum->goods_num;
                    }
                    $v->order_state  = self::getOrderState($v->order_id, $v->order_state, $v->refund_state, $v->evaluation_state);
                    $v->total_amount = $amount;
                    unset($v->refund_state);
                    unset($v->evaluation_state);
                }
            }
            return Base::jsonReturn(200, '获取成功', $data);
        }
        //0(已取消)10(默认):未付款;20:已付款;25:商家已接单;30:已发货;35骑手已接单40:已收货;
        //待支付；等待商家接单；商家已接单；商家正准备商品；骑手正赶往商家；骑手正在送货；订单已完成；订单已取消；待评价；退款中；退款成功
        static function getOrderState($order_id, $order_state, $refund_state, $evaluation_state)
        {
            if($order_state == 0) {
                return 1;
                // return "订单已取消";
            }
            if($order_state == 10) {
                return 2;
                //return "待支付";
            }
            if($order_state == 20) {
                return 3;
                //return "等待商家接单";
            }
            if($order_state == 25) {
                return 4;
                //return "商家已接单，正准备商品";
            }
            if($order_state == 35) {
                return 5;
                //return "骑手正赶往商家";
            }
            if($order_state == 30) {
                return 6;
                //return "骑手正在送货";
            }
            if($order_state == 40) {
                return 7;
                //return "订单已完成";
            }
            if($refund_state != 0) {
                $refund_state = BModel::getTableValue('refund_return', ['order_id' => $order_id], 'refund_state');
                if($refund_state != 3) {
                    return 8;//退款中
                }
                else {
                    return 9;//已完成
                }
            }
            if($order_state == 40 && $evaluation_state == 0) {
                return 10;
                //return "待评价";
            }
            if($order_state == 40 && $evaluation_state == 1) {
                return 11;
                //return "已评价";
            }
        }

        /**订单详情
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function orderInfo(Request $request)
        {
            $order_id = $request->input('order_id');
            if(!$order_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            $dat_count = DB::table('order as a')->leftJoin('order_common as b', 'a.order_id', 'b.order_id')->where('a.order_id', $order_id)->count();
            if($dat_count == 0) {
                return Base::jsonReturn(2000, '获取失败');
            }
            $result                = [];
            $order_data            = BModel::getTableFieldFirstData('order', ['order_id' => $order_id], ['store_id', 'order_state', 'shipping_fee', 'manjian_amount', 'order_sn', 'add_time', 'payment_code', 'refund_state', 'evaluation_state']);
            $store_data            = BModel::getTableFieldFirstData('store', ['store_id' => $order_data->store_id], ['store_name', 'store_phone']);
            $result['order_state'] = self::getOrderState($order_id, $order_data->order_state, $order_data->refund_state, $order_data->evaluation_state);//$order_data->order_state;
            $result['store_name']  = $store_data->store_name;
            $result['store_phone'] = is_null($store_data->store_phone) ? "" : $store_data->store_phone;

            $result['order_detail'] = DB::table('order_goods AS a')->leftJoin('order AS b', 'a.order_id', 'b.order_id')->leftJoin('order_common AS c', 'a.order_id', 'c.order_id')->where('b.order_id', $order_id)->get(['a.goods_id', 'a.goods_name', 'a.goods_price', 'a.goods_num', 'a.goods_image', 'c.voucher_code']);
            $amount                 = 0;
            foreach($result['order_detail'] as &$item) {
                if(!is_null($item->voucher_code)) {
                    $item->goods_marketprice = Member::getBLGoodsMarketprice($item->voucher_code);
                }
                else {
                    $item->goods_marketprice = BModel::getTableValue('goods', ['goods_id' => $item->goods_id], 'goods_marketprice');
                }
                $amount += $item->goods_price * $item->goods_num;
                unset($item->voucher_code);
            }

            $result['peisong']    = $order_data->shipping_fee;
            $result['manjian']    = $order_data->manjian_amount;
            $voucher_price        = BModel::getTableValue('order_common', ['order_id' => $order_id], 'voucher_price');
            $result['daijinquan'] = !$voucher_price ? '0.00' : $voucher_price;
            $result['total']      = Base::ncPriceFormat($amount + $result['peisong'] - $result['manjian'] - $result['daijinquan']);//应支付价格

            $receive_info = BModel::getTableFieldFirstData('order_common', ['order_id' => $order_id], ['reciver_name', 'reciver_info']);
            if(!$receive_info) {
                return Base::jsonReturn(2000, '获取失败');
            }
            $rec_data               = unserialize($receive_info->reciver_info);
            $result['peisong_info'] = ['username' => $receive_info->reciver_name, 'address' => $rec_data['address'], 'mobile' => $rec_data['phone'], 'sex' => !isset($rec_data['sex']) ? 1 : $rec_data['sex'],];
            $result['order_info']   = ['order_sn' => $order_data->order_sn, 'add_time' => date('Y-m-d H:i:s', $order_data->add_time), 'payment_code' => $order_data->payment_code,];
            unset($order_data->refund_state);
            unset($order_data->evaluation_state);
            return Base::jsonReturn(200, '获取成功', $result);
        }

        /**地区列表
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function areaList(Request $request)
        {
            $data = DB::table('area')->get()->toArray();

            foreach($data as $a) {
                $data['name'][$a->area_id]              = $a->area_name;
                $data['parent'][$a->area_id]            = $a->area_parent_id;
                $data['children'][$a->area_parent_id][] = $a->area_id;

                if($a->area_deep == 1 && $a->area_region) $data['region'][$a->area_region][] = $a->area_id;
            }
            $arr = [];
            foreach($data['children'][0] as $i) {
                $arr[$i] = $data['name'][$i];
            }
            $array = ["A" => [["pinyin" => "anhui", "abbr" => "AH", "name" => $arr[12]]], "C" => [["pinyin" => "chongqing", "abbr" => "CQ", "name" => $arr[22]]], "F" => [["pinyin" => "fujian", "abbr" => "FJ", "name" => $arr[13]]], "G" => [["pinyin" => "guangdong", "abbr" => "GD", "name" => $arr[19]], ["pinyin" => "gansu", "abbr" => "GS", "name" => $arr[28]], ["pinyin" => "guangxi", "abbr" => "GX", "name" => $arr[20]], ["pinyin" => "guizhou", "abbr" => "GZ", "name" => $arr[24]]], "H" => [["pinyin" => "hainan", "abbr" => "HN", "name" => $arr[21]], ["pinyin" => "hebei", "abbr" => "HB", "name" => $arr[3]], ["pinyin" => "henan", "abbr" => "HN", "name" => $arr[16]], ["pinyin" => "heilongjiang", "abbr" => "HLJ", "name" => $arr[8]]], "J" => [["pinyin" => "jiangsu", "abbr" => "JS", "name" => $arr[10]], ["pinyin" => "jiangxi", "abbr" => "JX", "name" => $arr[14]], ["pinyin" => "jilin", "abbr" => "JL", "name" => $arr[7]]], "N" => [["pinyin" => "neimenggu", "abbr" => "NMG", "name" => $arr[5]], ["pinyin" => "ningxia", "abbr" => "NX", "name" => $arr[30]]], "Q" => [["pinyin" => "qinghai", "abbr" => "QH", "name" => $arr[29]]], "S" => [["pinyin" => "shandong", "abbr" => "SD", "name" => $arr[15]], ["pinyin" => "shanxi", "abbr" => "SX", "name" => $arr[4]], ["pinyin" => "shanxi", "abbr" => "SX", "name" => $arr[27]], ["pinyin" => "sichuan", "abbr" => "SC", "name" => $arr[23]]], "X" => [["pinyin" => "xizang", "abbr" => "XZ", "name" => $arr[26]], ["pinyin" => "xinjiang", "abbr" => "XJ", "name" => $arr[31]]], "Y" => [["pinyin" => "yunnan", "abbr" => "YN", "name" => $arr[25]]], "Z" => [["pinyin" => "zhejiang", "abbr" => "ZJ", "name" => $arr[11]]],];
            return Base::jsonReturn(200, '获取成功', $array);

        }
        /////////////////////////

        /**全部分类
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function allSorts(Request $request)
        {
            $result        = [];
            $p_goods_class = BModel::getTableAllData('goods_class', ['gc_parent_id' => 0], ['gc_id', 'gc_name']);
            if(!$p_goods_class->isEmpty()) {
                foreach($p_goods_class as $key => $value) {
                    $result[$key]['stc_id']   = $value->gc_id;
                    $result[$key]['stc_name'] = $value->gc_name;
                    $result[$key]['child']    = BModel::getTableAllData('goods_class', ['gc_parent_id' => $value->gc_id], ['gc_id as stc_id', 'gc_name as stc_name']);
                }
            }
            return Base::jsonReturn(200, '获取成功', $result);
        }

        /**取消收藏
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function cancelCollect(Request $request)
        {
            $member_id    = $request->input('member_id');
            $favorites_id = $request->input('favorites_id');
            if(!$member_id || !$favorites_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!Member::checkExist('member', ['member_id' => $member_id])) {
                return Base::jsonReturn(2000, '用户不存在');
            }
            if(!Member::checkExist('favorites', ['log_id' => $favorites_id])) {
                return Base::jsonReturn(2001, '收藏不存在');
            }
            $result = BModel::delData('favorites', ['log_id' => $favorites_id]);
            if($result) {
                return Base::jsonReturn(200, '取消成功');
            }
            else {
                return Base::jsonReturn(2002, '取消失败');
            }
        }

        /**收藏列表
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function userCollectList(Request $request)
        {
            $member_id = $request->input('member_id');
            $longitude = $request->input('longitude');
            $dimension = $request->input('dimension');
            if(!$member_id || !$longitude || !$dimension) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!Member::checkExist('member', ['member_id' => $member_id])) {
                return Base::jsonReturn(2000, '用户不存在');
            }
            $storeIds = BModel::getTableAllData('favorites', ['member_id' => $member_id], ['store_id', 'log_id as favorites_id']);
            $result   = [];
            if(!$storeIds->isEmpty()) {
                foreach($storeIds as $k => $storeId) {

                    $store_data               = BModel::getTableFieldFirstData('store', ['store_id' => $storeId->store_id], ['store_id', 'store_name', 'store_avatar', 'store_sales', 'store_credit', 'longitude', 'dimension']);
                    $lucheng                  = BaseController::getdistance($longitude, $dimension, $store_data->longitude, $store_data->dimension);
                    $store_data->favorites_id = $storeId->favorites_id;
                    $store_data->qisong       = "10";
                    $store_data->peisong      = "15";
                    if($lucheng < 1000) {
                        $store_data->distanct = ceil($lucheng)."米";
                    }
                    else {
                        $store_data->distanct = round($lucheng / 1000, 2)."公里"; //10.46;
                    }
                    $shijian = ($lucheng / 3) / 60;
                    if($shijian < 60) {
                        $store_data->need_time = ceil($shijian)."分";
                    }
                    else {
                        $store_data->need_time = floor($shijian / 60)."小时".ceil($shijian % 60)."分";
                    }
                    $result[$k]['store_data'] = $store_data;
                    unset($store_data->longitude);
                    unset($store_data->dimension);
                }
            }
            return Base::jsonReturn(200, '获取成功', $result);
        }

        /**取消订单
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function cancelOrder(Request $request)
        {
            $member_id = $request->input('member_id');
            $order_id  = $request->input('order_id');
            if(!$member_id || !$order_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!Member::checkExist('member', ['member_id' => $member_id])) {
                return Base::jsonReturn(2000, '用户不存在');
            }
            if(!Member::checkExist('order', ['order_id' => $order_id])) {
                return Base::jsonReturn(2001, '订单不存在');
            }
            $result = BModel::upTableData('order', ['order_id' => $order_id], ['order_state' => 0]);
            if($result) {
                return Base::jsonReturn(200, '取消成功');
            }
            else {
                return Base::jsonReturn(2002, '取消失败');
            }

        }


        /**店铺详情
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function storeInfo(Request $request)
        {
            $store_id  = $request->input('store_id');
            $member_id = $request->input('member_id');
            $result    = [];
            if(!$store_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(BModel::getCount('store', ['store_id' => $store_id]) == 0) {
                return Base::jsonReturn(1001, '店铺不存在');
            }
            //店铺详情
            $store_info           = BModel::getTableFieldFirstData('store', ['store_id' => $store_id], ['store_id', 'store_name', 'store_avatar', 'store_sales', 'store_credit', 'store_description']);
            $result['store_info'] = $store_info;
            //是否收藏
            if(!$member_id) {
                $count                = BModel::getCount('favorites', ['member_id' => $member_id, 'fav_type' => 'store', 'store_id' => $store_id]);
                $result['is_collect'] = $count == 1 ? true : false;
            }
            else {
                $result['is_collect'] = false;
            }
            $manjian           = BModel::getLeftData('p_mansong_rule AS a', 'p_mansong AS b', 'a.mansong_id', 'b.mansong_id', ['b.store_id' => $store_id], ['a.price', 'a.discount']);
            $result['manjian'] = $manjian->isEmpty() ? [] : $manjian->toArray();
            $class_list        = Store::getAllStoreClass(['store_id' => $store_id], ['stc_id', 'stc_name']);
            $calssLists        = [];
            if(!$class_list->isEmpty()) {
                $calssList = $class_list->toArray();
                foreach($calssList as $k => $val) {
                    $calssLists[$k]['stc_id']    = (string)$val->stc_id;
                    $calssLists[$k]['stc_name']  = (string)$val->stc_name;
                    $calssLists[$k]['cart_nums'] = Member::getCartGoodsNum($store_id, $val->stc_id, $member_id);
                }
            }
            //$goods_list = Member::getStoreGoodsListByStcId($store_id, $class_id);
            $result['goods_list'] = $calssLists;

            foreach($result['goods_list'] as $k => &$m) {
                $m['goods'] = Member::getStoreGoodsListByStcId($store_id, $m['stc_id'], $member_id);
            }
            $taozhuang = Member::getStoreGoodsListByStcId($store_id, 'taozhuang');
            if($taozhuang) {
                array_unshift($result['goods_list'], ['stc_id' => "taozhuang", 'stc_name' => "优惠", 'cart_nums' => Member::getTaozhuangCartGoodsNum($store_id, $member_id), 'goods' => $taozhuang]);
            }

            $xianshi = Member::getStoreGoodsListByStcId($store_id, 'xianshi');
            if($xianshi) {
                array_unshift($result['goods_list'], ['stc_id' => "xianshi", 'stc_name' => "折扣", 'cart_nums' => Member::getXianshiCartGoodsNum($store_id, $member_id), 'goods' => $xianshi]);
            }

            array_unshift($result['goods_list'], ['stc_id' => "hot", 'stc_name' => "热销", 'cart_nums' => Member::getHotCartGoodsNum($store_id, $member_id), 'goods' => Member::getStoreGoodsListByStcId($store_id, 'hot')]);
            $result['cart'] = ['peisong' => 5, 'goods' => Member::getCartGoods($store_id, $member_id)];
            //        $result['cart']['nums'] = BModel::getCount('cart', ['store_id' => $store_id]);
            //        $result['cart']['amount'] = BModel::getSum('cart', ['store_id' => $store_id], 'goods_price');
            $result['comment_url']    = getenv('HOST_URL').'/users/#/evaluate/'.$store_id.'/evaluateall';
            $result['store_info_url'] = getenv('HOST_URL').'/users/#/business/'.$store_id;
            return Base::jsonReturn(200, '获取成功', $result);
        }

        /**返回统一下单
         *
         * @param Request $request
         * @return array
         */
        function wxPay($amount, $order_sn)
        {
            $nonce_str                = md5(microtime()); // uuid 生成随机不重复字符串
            $data['appid']            = config('wxpay.appid'); //appid
            $data['mch_id']           = config('wxpay.mch_id'); //商户ID
            $data['nonce_str']        = $nonce_str; //随机字符串 这个随便一个字符串算法就可以，我是使用的UUID
            $data['body']             = "商品描述"; // 商品描述
            $data['out_trade_no']     = $order_sn;    //商户订单号,不能重复
            $data['total_fee']        = $amount * 100; //金额
            $data['spbill_create_ip'] = $_SERVER['SERVER_ADDR'];   //ip地址
            $data['notify_url']       = config('wxpay.notify_url');   //回调地址,用户接收支付后的通知,必须为能直接访问的网址,不能跟参数
            $data['trade_type']       = config('wxpay.trade_type');      //支付方式
            //将参与签名的数据保存到数组  注意：以上几个参数是追加到$data中的，$data中应该同时包含开发文档中要求必填的剔除sign以外的所有数据
            $data['sign'] = $this->getSign($data);        //获取签名
            $xml          = $this->ToXml($data);            //数组转xml
            //curl 传递给微信方
            $url  = "https://api.mch.weixin.qq.com/pay/unifiedorder";
            $data = $this->curl($url, $xml, []); // 请求微信生成预支付订单
            //返回结果
            if($data) {
                //返回成功,将xml数据转换为数组.
                $re = $this->FromXml($data);
                if($re['return_code'] != 'SUCCESS') {
                    return [];
                }
                else {
                    //接收微信返回的数据,传给APP!
                    $arr = ['prepayid' => $re['prepay_id'], // 用返回的数据
                        'appid' => config('wxpay.appid'), 'partnerid' => config('wxpay.mch_id'), // 商户ID
                        'package' => 'Sign=WXPay', 'noncestr' => $nonce_str, 'timestamp' => time(),];
                    //第二次生成签名
                    $sign        = $this->getSign($arr);
                    $arr['sign'] = $sign;
                    return $arr;
                }
            }
            else {
                return [];
            }
        }

        function getSign($params)
        {
            ksort($params);        //将参数数组按照参数名ASCII码从小到大排序
            foreach($params as $key => $item) {
                if(!empty($item)) {         //剔除参数值为空的参数
                    $newArr[] = $key.'='.$item;     // 整合新的参数数组
                }
            }
            $stringA        = implode("&", $newArr);         //使用 & 符号连接参数
            $stringSignTemp = $stringA."&key=".config('wxpay.key');        //拼接key
            // key是在商户平台API安全里自己设置的
            $stringSignTemp = MD5($stringSignTemp);       //将字符串进行MD5加密
            $sign           = strtoupper($stringSignTemp);      //将所有字符转换为大写
            return $sign;
        }

        function ToXml($data = [])
        {
            if(!is_array($data) || count($data) <= 0) {
                return "";
            }

            $xml = "<xml>";
            foreach($data as $key => $val) {
                if(is_numeric($val)) {
                    $xml .= "<".$key.">".$val."</".$key.">";
                }
                else {
                    $xml .= "<".$key."><![CDATA[".$val."]]></".$key.">";
                }
            }
            $xml .= "</xml>";
            return $xml;
        }

        function curl($url = '', $request = [], $header = [], $method = 'POST')
        {
            $header[] = 'Accept-Encoding: gzip, deflate';//gzip解压内容
            $ch       = curl_init();   //1.初始化
            curl_setopt($ch, CURLOPT_URL, $url); //2.请求地址
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);//3.请求方式
            //4.参数如下
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//https
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');//模拟浏览器
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

            if($method == "POST") {//5.post方式的时候添加数据
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $tmpInfo = curl_exec($ch);//6.执行

            if(curl_errno($ch)) {//7.如果出错
                return curl_error($ch);
            }
            curl_close($ch);//8.关闭
            return $tmpInfo;
        }

        function FromXml($xml)
        {
            if(!$xml) {
                echo "xml数据异常！";
            }
            //将XML转为array
            //禁止引用外部xml实体
            libxml_disable_entity_loader(true);
            $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            return $data;
        }

        /**
         * 回调地址
         */
        function wx_notify()
        {
            //接收微信返回的数据数据,返回的xml格式
            $xmlData = file_get_contents('php://input');
            //将xml格式转换为数组
            $data = $this->FromXml($xmlData);
            //用日志记录检查数据是否接受成功，验证成功一次之后，可删除。
            $sign = $data['sign'];
            unset($data['sign']);
            if($sign == $this->getSign($data)) {
                //签名验证成功后，判断返回微信返回的
                if($data['result_code'] == 'SUCCESS') {
                    //根据返回的订单号做业务逻辑
                    /////////////////////////////////////////////////////////////////
                    //处理完成之后，告诉微信成功结果！
                    $result = "5";//订单状态处理
                    if($result) {
                        echo '<xml>
              <return_code><![CDATA[SUCCESS]]></return_code>
              <return_msg><![CDATA[OK]]></return_msg>
              </xml>';
                        exit();
                    }
                } //支付失败，输出错误信息
                else {
                    $file = fopen('./log.txt', 'a+');
                    fwrite($file, "错误信息：".$data['return_msg'].date("Y-m-d H:i:s"), time()."\r\n");
                }
            }
            else {
                $file = fopen('./log.txt', 'a+');
                fwrite($file, "错误信息：签名验证失败".date("Y-m-d H:i:s"), time()."\r\n");
            }
        }

        /**意见反馈
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function userFeedback(Request $request)
        {
            $member_id           = $request->input('member_id');
            $message             = $request->input('message');
            $contact_information = $request->input('contact_information');

            if(!$member_id || !$message || !$contact_information) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!Member::checkExist('member', ['member_id' => $member_id])) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            $ins_data = ['ftime' => time(), 'content' => $message, 'type' => 1, 'member_id' => $member_id, 'member_name' => BModel::getTableValue('member', ['member_id' => $member_id], 'member_name'), 'mobile' => $contact_information];
            $result   = BModel::insertData('mb_feedback', $ins_data);
            if($result) {
                return Base::jsonReturn(200, '反馈成功');
            }
            else {
                return Base::jsonReturn(2002, '反馈失败');
            }
        }

        /**我的代金券
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function userVoucherList(Request $request)
        {
            $member_id = $request->input('member_id');
            $type      = $request->input('type');

            if(!$member_id || !$type) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!Member::checkExist('member', ['member_id' => $member_id])) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            $data  = [];
            $field = ['voucher_id', 'a.voucher_limit', 'a.voucher_price', 'b.store_name', 'a.voucher_end_date', 'a.voucher_state as voucher_t_state'];
            if($type == 1) {
                //未使用
                $result = DB::table('voucher as a')->leftJoin('store as b', 'a.voucher_store_id', 'b.store_id')->where('a.voucher_owner_id', $member_id)->where('a.voucher_state', 1)->where('a.voucher_end_date', '<', time())->get($field);
                if(!$result->isEmpty()) {
                    foreach($result as $k => $val) {
                        $data[$k]['voucher_t_id']        = $val->voucher_id;
                        $data[$k]['voucher_t_eachlimit'] = $val->voucher_limit;
                        $data[$k]['voucher_t_end_date']  = date('Y-m-d H:i:s', $val->voucher_end_date);
                        $data[$k]['voucher_t_price']     = Base::ncPriceFormat($val->voucher_price);
                        $data[$k]['store_name']          = $val->store_name;
                        $data[$k]['voucher_t_state']     = "未使用";
                    }
                }

            }
            else if($type == 2) {
                //已使用
                $result = DB::table('voucher as a')->leftJoin('store as b', 'a.voucher_store_id', 'b.store_id')->where('a.voucher_owner_id', $member_id)->where('a.voucher_state', 2)->whereNotNull('a.voucher_order_id')->get($field);
                if(!$result->isEmpty()) {
                    foreach($result as $k => $val) {
                        $data[$k]['voucher_t_id']        = $val->voucher_id;
                        $data[$k]['voucher_t_eachlimit'] = $val->voucher_limit;
                        $data[$k]['voucher_t_end_date']  = date('Y-m-d H:i:s', $val->voucher_end_date);
                        $data[$k]['voucher_t_price']     = Base::ncPriceFormat($val->voucher_price);
                        $data[$k]['store_name']          = $val->store_name;
                        $data[$k]['voucher_t_state']     = "已使用";
                    }
                }
            }
            else {
                //已过期
                $result = DB::table('voucher as a')->leftJoin('store as b', 'a.voucher_store_id', 'b.store_id')->where('a.voucher_owner_id', $member_id)->where('a.voucher_state', 3)->where('a.voucher_end_date', '<', time())->get($field);
                if(!$result->isEmpty()) {
                    foreach($result as $k => $val) {
                        $data[$k]['voucher_t_id']        = $val->voucher_id;
                        $data[$k]['voucher_t_eachlimit'] = $val->voucher_limit;
                        $data[$k]['voucher_t_end_date']  = date('Y-m-d H:i:s', $val->voucher_end_date);
                        $data[$k]['voucher_t_price']     = Base::ncPriceFormat($val->voucher_price);
                        $data[$k]['store_name']          = $val->store_name;
                        $data[$k]['voucher_t_state']     = "已过期";
                    }
                }
            }
            return Base::jsonReturn(200, '获取成功', $data);
        }

        /**用户评价
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function userComment(Request $request)
        {
            $member_id = $request->input('member_id');

            if(!$member_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!Member::checkExist('member', ['member_id' => $member_id])) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            $result = [];
            $data   = BModel::getTableAllData('store_com', ['member_id' => $member_id, 'parent_id' => 0]);
            if(!$data->isEmpty()) {
                foreach($data as $k => $v) {
                    $member_data                 = BModel::getTableFieldFirstData('member', ['member_id' => $member_id], ['member_name', 'member_avatar']);
                    $result[$k]['com_id']        = $v->com_id;
                    $result[$k]['content']       = is_null($v->content) ? "" : $v->content;
                    $result[$k]['images']        = !$v->images ? [] : explode(',', $v->images);
                    $result[$k]['kouwei']        = is_null($v->kouwei) ? 0 : $v->kouwei;
                    $result[$k]['baozhuang']     = is_null($v->baozhuang) ? 0 : $v->baozhuang;
                    $result[$k]['peisong']       = is_null($v->peisong) ? 0 : $v->peisong;
                    $result[$k]['haoping']       = is_null($v->haoping) ? 0 : $v->haoping;
                    $result[$k]['member_name']   = is_null($member_data->member_name) ? "" : $member_data->member_name;
                    $result[$k]['member_avator'] = is_null($member_data->member_avatar) ? "" : $member_data->member_avatar;
                    $result[$k]['add_time']      = date('Y-m-d H:i:s', $v->add_time);
                    if($v->is_replay != 0) {
                        $store_data           = BModel::getTableFieldFirstData('store', ['store_id' => $v->store_id], ['store_id', 'store_name', 'store_avatar']);
                        $result[$k]['replay'] = ['store_avator' => $store_data->store_avatar, 'store_name' => $store_data->store_name, 'store_id' => $store_data->store_id];
                    }
                    else {
                        $result[$k]['replay'] = (object)[];
                    }
                }
            }
            return Base::jsonReturn(200, '获取成功', $result);
        }

        /**主页搜索
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function search(Request $request)
        {
            $keywords  = $request->input('keywords');
            $type      = $request->input('type');
            $longitude = $request->input('longitude');
            $latitude  = $request->input('latitude');

            if(!$keywords || !$type || !$longitude || !$latitude) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            $data = [];
            if($type == 1) {
                $data = Member::getDefaultStoreList($keywords, $longitude, $latitude);
            }
            else if($type == 2) {
                $data = Member::getCreditStoreList($keywords, $longitude, $latitude);
            }
            else if($type == 3) {
                $data = Member::getLocalStoreList($keywords, $longitude, $latitude);
            }
            else {
                $data = Member::getBestStoreList($keywords, $longitude, $latitude);
            }
            return Base::jsonReturn(200, '获取成功', $data);
        }

        /**等待支付
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function waitingPay(Request $request)
        {
            $order_id  = $request->input('order_id');
            $member_id = $request->input('member_id');

            if(!$order_id || !$member_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!Member::checkExist('order', ['order_id' => $order_id])) {
                return Base::jsonReturn(1001, '订单不存在');
            }
            $exp_time = BModel::getTableValue('order', ['order_id' => $order_id], 'add_time');
            if($exp_time + 15 * 60 <= time()) {
                BModel::upTableData('order', ['order_id' => $order_id], ['order_state' => 0]);
                return Base::jsonReturn(1001, '订单已超时');
            }
            $result                 = [];
            $order_data             = BModel::getTableFieldFirstData('order', ['order_id' => $order_id], ['store_id', 'order_state', 'shipping_fee', 'manjian_amount', 'order_sn', 'add_time', 'payment_code', 'refund_state', 'evaluation_state']);
            $result['order_detail'] = DB::table('order_goods AS a')->leftJoin('order AS b', 'a.order_id', 'b.order_id')->leftJoin('order_common AS c', 'a.order_id', 'c.order_id')->where('b.order_id', $order_id)->get(['a.goods_id', 'a.goods_name', 'a.goods_price', 'a.goods_num', 'c.voucher_code']);
            $amount                 = 0;
            foreach($result['order_detail'] as &$item) {
                $amount += $item->goods_price * $item->goods_num;
                unset($item->voucher_code);
            }
            $result['peisong']    = $order_data->shipping_fee;
            $result['baozhuang']  = "0.00";
            $result['manjian']    = $order_data->manjian_amount;
            $voucher_price        = BModel::getTableValue('order_common', ['order_id' => $order_id], 'voucher_price');
            $result['daijinquan'] = !$voucher_price ? '0.00' : $voucher_price;
            $result['total']      = Base::ncPriceFormat($amount + $result['peisong'] - $result['manjian'] - $result['daijinquan']);//应支付价格

            $receive_info = BModel::getTableFieldFirstData('order_common', ['order_id' => $order_id], ['reciver_name', 'reciver_info']);
            if(!$receive_info) {
                return Base::jsonReturn(2000, '获取失败');
            }
            $rec_data               = unserialize($receive_info->reciver_info);
            $result['peisong_info'] = ['username' => $receive_info->reciver_name, 'address' => $rec_data['address'], 'mobile' => $rec_data['phone'], 'sex' => !isset($rec_data['sex']) ? 1 : $rec_data['sex'],];
            unset($order_data->refund_state);
            unset($order_data->evaluation_state);
            //$order_sn         = BModel::getTableValue('order', ['order_id' => $order_id], 'order_sn');
            //$result['wx_pay'] = $this->wxPay($amount, $order_sn);
            return Base::jsonReturn(200, '获取成功', $result);
        }

        /**搜索分类
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function searchClass(Request $request)
        {
            $gc_id     = $request->input('gc_id');
            $sc_id     = $request->input('sc_id');
            $type      = $request->input('type');
            $longitude = $request->input('longitude');
            $latitude  = $request->input('latitude');
            if(!$gc_id || !$longitude || !$latitude) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            $data               = [];
            $sorts_list         = BModel::getTableAllData('goods_class', ['gc_parent_id' => $gc_id], ['gc_id', 'gc_name']);
            $data['sorts_list'] = $sorts_list->isEmpty() ? [] : $sorts_list->toArray();
            if(!$sc_id) {
                $ids  = [];
                $list = BModel::getTableAllData('goods_class', ['gc_parent_id' => $gc_id], ['gc_id']);
                if(!$list->isEmpty()) {
                    foreach($list as $v) {
                        $ids[]     = $v->gc_id;
                        $child_ids = BModel::getTableAllData('goods_class', ['gc_parent_id' => $v->gc_id], ['gc_id']);
                        if(!$child_ids->isEmpty()) {
                            foreach($child_ids as $val) {
                                $ids[] = $val->gc_id;
                            }

                        }
                    }
                }
            }
            else {
                $ids  = [];
                $list = BModel::getTableAllData('goods_class', ['gc_parent_id' => $sc_id], ['gc_id']);
                if(!$list->isEmpty()) {
                    foreach($list as $v) {
                        $ids[] = $v->gc_id;
                    }
                }
            }
            $ids = array_unique($ids);
            if($type == 1) {
                $data['store_list'] = Member::getDefaultKJStoreList($ids, $longitude, $latitude);
            }
            else if($type == 2) {
                $data['store_list'] = Member::getCreditKJStoreList($ids, $longitude, $latitude);
            }
            else if($type == 3) {
                $data['store_list'] = Member::getLocalKJStoreList($ids, $longitude, $latitude);
            }
            else {
                $data['store_list'] = Member::getBestKJStoreList($ids, $longitude, $latitude);
            }
            return Base::jsonReturn(200, '获取成功', $data);
        }

        /**确认收货
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function confirmOrder(Request $request)
        {
            $order_id  = $request->input('order_id');
            $member_id = $request->input('member_id');

            if(!$order_id || !$member_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!Member::checkExist('order', ['order_id' => $order_id])) {
                return Base::jsonReturn(1001, '订单不存在');
            }
            $result = BModel::upTableData('order', ['buyer_id' => $member_id, 'order_id' => $order_id], ['order_state' => 40]);
            if($result) {
                return Base::jsonReturn(200, '收货成功');
            }
            else {
                return Base::jsonReturn(2000, '收货失败');
            }
        }

        /**查询订单状态
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function getOrderStates(Request $request)
        {
            $member_id = $request->input('member_id');
            if(!$member_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!Member::checkExist('member', ['member_id' => $member_id])) {
                return Base::jsonReturn(1001, '用户不存在');
            }
            $order_state = DB::table('order')->where('buyer_id', $member_id)->orderBy('order_id', 'desc')->limit(1)->value('order_state');
            $result      = [];
            if(isset($order_state)) {
                $result = ['order_state' => self::_getOrderState($order_state), 'time' => date('H:i')];
            }
            else {
                $result = (object)[];
            }
            return Base::jsonReturn(200, '查询成功', $result);
        }

        static function _getOrderState($order_state)
        {
            if($order_state == 0) {
                return "订单已取消";
            }
            if($order_state == 10) {
                return "待支付";
            }
            if($order_state == 20) {
                return "等待商家接单";
            }
            if($order_state == 25) {
                return "商家已接单，正准备商品";
            }
            if($order_state == 35) {
                return "骑手已接单,正赶往商家";
            }
            if($order_state == 30) {
                return "骑手正在送货";
            }
            if($order_state == 40) {
                return "订单已完成";
            }
        }

        /**退款原因
         *
         * @return \Illuminate\Http\JsonResponse
         */
        static function reasonList()
        {
            $data = DB::table('refund_reason')->get(['reason_id', 'reason_info']);
            return Base::jsonReturn(200, '获取成功', $data->isEmpty() ? [] : $data->toArray());
        }

        /**微信登录
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function wxLogin(Request $request)
        {
            $code   = $request->input('code');
            $openid = self::getOpenID(config('wxpay.appid'), config('wxpay.appsecret'), $code);
            if($openid) {
                $data = BModel::getTableFieldFirstData('member', ['member_wxopenid' => $openid], ['member_id', 'member_mobile', 'member_name', 'member_avatar', 'member_wxopenid']);
                if(!$data) {
                    $ins_data  = ['member_name' => '未设置_'.microtime(), 'member_mobile_bind' => 0, 'member_time' => time(), 'member_wxopenid' => $openid];
                    $member_id = BModel::insertData('member', $ins_data);
                    BModel::insertData('member_common', ['member_id' => $member_id]);
                }
                $member_info                 = BModel::getTableFieldFirstData('member', ['member_wxopenid' => $openid], ['member_id', 'member_passwd', 'member_mobile', 'member_name', 'member_avatar', 'member_wxopenid']);
                $member_info->member_avatar  = is_null($member_info->member_avatar) ? '' : $member_info->member_avatar;
                $member_info->need_pwd       = empty($member_info->member_passwd) ? true : false;
                $member_info->is_bind_openid = empty($member_info->member_wxopenid) ? false : true;
                $member_info->token          = Base::makeToken(microtime());
                $token_data                  = ['member_id' => $member_info->member_id, 'token' => $member_info->token, 'add_time' => time(), 'expire_time' => time() + 24 * 5 * 3600];
                Token::addToken($token_data);
                return Base::jsonReturn(200, '登录成功', $member_info);
            }
            else {
                return Base::jsonReturn(2000, '登录失败');
            }
        }

        static function getOpenID($appid, $appsecret, $code)
        {
            $url        = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
            $weixin     = file_get_contents($url);//通过code换取网页授权access_token
            $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
            $array      = get_object_vars($jsondecode);//转换成数组
            $openid     = $array['openid'];//输出openid
            return $openid;
        }

        /**售后详情
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function refundInfo(Request $request)
        {
            $refund_id = $request->input('refund_id');
            if(!$refund_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!Member::checkExist('refund_return', ['refund_id' => $refund_id])) {
                return Base::jsonReturn(1001, '数据不存在');
            }
            $result = [];
            $data   = BModel::getTableFirstData('refund_return', ['refund_id' => $refund_id]);
            if($data) {
                $result['refund_state']  = self::getRefundState($data->refund_state);
                $result['refund_amount'] = $data->refund_amount;
                $result['order_sn']      = $data->order_sn;
                $result['reason_info']   = $data->reason_info;
                if($data->order_goods_id == 0) {
                    $goods      = [];
                    $goods_data = BModel::getTableAllData('order_goods', ['order_id' => $data->order_id]);
                    if(!$goods_data->isEmpty()) {
                        foreach($goods_data as $k => $goods_datum) {
                            $goods[$k]['goods_name']  = $goods_datum->goods_name;
                            $goods[$k]['goods_num']   = $goods_datum->goods_num;
                            $goods[$k]['goods_image'] = $goods_datum->goods_image;
                            $goods[$k]['goods_price'] = $goods_datum->goods_price;
                        }
                    }
                    $result['goods_list'] = $goods;
                }
                else {
                    $goods = [['goods_name' => $data->goods_name, 'goods_num' => $data->goods_num, 'goods_image' => is_null($data->goods_image)?"":$data->goods_image,'goods_price'=>BModel::getTableValue('goods',['goods_id'=>$data->order_goods_id],'goods_price')]];

                    $result['goods_list'] = $goods;
                }

                return Base::jsonReturn(200, '获取成功', $result);
            }
            else {
                return Base::jsonReturn(2000, '获取失败');
            }
        }

        static function getRefundState($refund_state)
        {
            if($refund_state == 1) {
                return '处理中';
            }
            else if($refund_state == 2) {
                return '待管理员处理';
            }
            else if($refund_state == 3) {
                return '已完成';
            }
        }

        /**骑手入驻
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        function qishouJoinin(Request $request)
        {
            $refund_id = $request->input('refund_id');
            if(!$refund_id) {
                return Base::jsonReturn(1000, '参数缺失');
            }
            if(!Member::checkExist('refund_return', ['refund_id' => $refund_id])) {
                return Base::jsonReturn(1001, '数据不存在');
            }

        }


    }
