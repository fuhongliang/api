<?php

namespace App\Http\Controllers\U1;


use App\BModel;
use App\Http\Controllers\BaseController as Base;
use App\model\U1\Member;
use App\model\V3\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MemberController extends Base
{
    /**首页
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function homePage(Request $request)
    {
        $keyword                  = $request->input('keyword');
        $page                     = $request->input('page');
        $result                   = [];
        $result['banner_data']    = BModel::getOrderData('app_banner', 'sort', ['title', 'image_name', 'link_url']);
        $result['gcsort_data']    = Member::getParentGoodsClass();
        $result['discount_data']  = Member::getAppDiscount();
        $page                     = !$page ? 1 : $page;
        $result['storelist_data'] = Member::getStoreList($keyword, $page);

        return Base::jsonReturn('200', '获取成功', $result);
    }

    /**验证码登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function smsLogin(Request $request)
    {
        $phone_number  = $request->input('mobile');
        $verify_code   = $request->input('code');
        $device_tokens = $request->input('device_tokens');
        $app_type      = $request->input('app_type');
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
            $member_id   = $member_data->member_id;
            BModel::delData('umeng', ['member_id' => $member_id]);
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
            $ins_data  = array(
                'member_mobile' => $phone_number,
                'member_name' => '未设置_' . time(),
                'member_mobile_bind' => 1,
                'member_time' => time()
            );
            $need_pwd  = true;
            $member_id = BModel::insertData('member', $ins_data);
            BModel::insertData('member_common', ['member_id' => $member_id]);
        }
        BModel::insertData('umeng', ['app_type' => $app_type, 'device_tokens' => $device_tokens, 'member_id' => $member_id]);
        $member_info           = BModel::getTableFirstData('member', ['member_id' => $member_id], ['member_mobile', 'member_name', 'member_avatar']);
        $member_info->need_pwd = $need_pwd;
        return Base::jsonReturn('200', '登录成功', $member_info);
    }

    /**账号密码登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function userLogin(Request $request)
    {
        $phone_number  = $request->input('mobile');
        $password      = $request->input('password');
        $device_tokens = $request->input('device_tokens');
        $app_type      = $request->input('app_type');
        if (empty($phone_number) || empty($password) || empty($device_tokens) || empty($app_type)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (!preg_match("/^1[34578]{1}\d{9}$/", $phone_number)) {
            return Base::jsonReturn(1000, '手机号格式不正确');
        }
        if (BModel::getCount('member', ['member_mobile' => $phone_number]) == 1) {
            $member_data = BModel::getTableFirstData('member', ['member_mobile' => $phone_number], ['member_passwd', 'member_id']);
            if (md5($password) != $member_data->member_passwd) {
                return Base::jsonReturn('1001', '账号或密码错误');
            }
            BModel::delData('umeng', ['member_id' => $member_data->member_id]);
            BModel::insertData('umeng', ['app_type' => $app_type, 'device_tokens' => $device_tokens, 'member_id' => $member_data->member_id]);
            $up_data = array(
                'member_login_time' => time(),
                'member_login_ip' => $request->getClientIp()
            );
            BModel::upTableData('member', ['member_id' => $member_data->member_id], $up_data);
            $member_info = BModel::getTableFirstData('member', ['member_id' => $member_data->member_id], ['member_mobile', 'member_name', 'member_avatar']);
            return Base::jsonReturn('200', '登录成功', $member_info);
        } else {
            return Base::jsonReturn('1002', '用户不存在');
        }
    }

    /**添加密码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function userAddPwd(Request $request)
    {
        $member_id = $request->input('member_id');
        $password  = $request->input('password');
        if (empty($member_id) || empty($password)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (BModel::getCount('member_id', ['member_id' => $member_id]) == 0) {
            return Base::jsonReturn(1001, '用户不存在');
        }
        $res = BModel::upTableData('member', ['member_id' => $member_id], ['member_passwd' => md5($password)]);
        if ($res) {
            return Base::jsonReturn('200', '添加成功');
        } else {
            return Base::jsonReturn('1002', '添加失败');
        }
    }

    /**收货地址列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function userAddrList(Request $request)
    {
        $member_id = $request->input('member_id');
        if (empty($member_id)) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (BModel::getCount('member_id', ['member_id' => $member_id]) == 0) {
            return Base::jsonReturn(1001, '用户不存在');
        }
        $res = BModel::getTableAllData('address', ['member_id' => $member_id], ['area_info', 'address', 'mob_phone', 'sex', 'true_name', 'is_default']);

        return Base::jsonReturn('200', '获取成功', $res->isEmpty() ? array() : $res->toArray());
    }

    /**添加收货地址
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function userAddrAdd(Request $request)
    {
        $member_id   = $request->input('member_id');
        $true_name   = $request->input('true_name');
        $sex         = $request->input('sex');
        $mob_phone   = $request->input('mobile_phone');
        $province_id = $request->input('province_id');
        $city_id     = $request->input('city_id');
        $area_id     = $request->input('area_id');
        $address     = $request->input('address');
        if (!$member_id || !$true_name || !$sex || !$mob_phone || !$province_id || !$city_id || !$area_id || !$address) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        if (BModel::getCount('member_id', ['member_id' => $member_id]) == 0) {
            return Base::jsonReturn(1001, '用户不存在');
        }
        $province = BModel::getTableValue('area', ['area_id' => $province_id], 'area_name');
        $city     = BModel::getTableValue('area', ['area_id' => $city_id], 'area_name');
        $area     = BModel::getTableValue('area', ['area_id' => $area_id], 'area_name');
        $ins_data = array(
            'member_id' => $member_id,
            'true_name' => $true_name,
            'area_id' => $area_id,
            'city_id' => $city_id,
            'area_info' => $province . $city . $area,
            'address' => $address,
            'mob_phone' => $mob_phone,
            'sex' => $sex
        );
        $result   = BModel::insertData('address', $ins_data);
        if ($result) {
            return Base::jsonReturn('200', '添加成功');
        } else {
            return Base::jsonReturn('1002', '添加失败');
        }
    }

    /**店铺详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function storeInfo(Request $request)
    {
        $store_id  = $request->input('store_id');
        $member_id = $request->input('member_id');
        $class_id  = $request->input('class_id');
        $result    = [];
        //店铺详情
        $store_info           = BModel::getTableFieldFirstData('store', ['store_id' => $store_id], ['store_id', 'store_name', 'store_avatar', 'store_sales', 'store_credit', 'store_description']);
        $result['store_info'] = !$store_info ? [] : $store_info;
        //是否收藏
        $count                = BModel::getCount('favorites', ['member_id' => $member_id, 'fav_type' => 'store', 'store_id' => $store_id]);
        $result['is_collect'] = $count == 1 ? true : false;
        $manjian              = BModel::getLeftData('p_mansong_rule AS a', 'p_mansong AS b', 'a.mansong_id', 'b.mansong_id', ['b.store_id' => $store_id], ['a.price', 'a.discount']);
        $result['manjian']    = $manjian->isEmpty() ? [] : $manjian->toArray();
        $result['daijinquan'] = Member::getStoreVoucher($store_id);
        //
        $class_list = Store::getAllStoreClass(['store_id' => $store_id], ['stc_id', 'stc_name']);
        $calssLists = [];
        if (!$class_list->isEmpty()) {
            $calssList = $class_list->toArray();
            foreach ($calssList as $k => $val) {
                $calssLists[$k]['stc_id']   = (string)$val->stc_id;
                $calssLists[$k]['stc_name'] = (string)$val->stc_name;
            }
        }
        $goods_list           = Member::getStoreGoodsListByStcId($store_id, $class_id);
        $result['class_list'] = $calssLists;
        $result['goods_list'] = empty($goods_list) ? array() : $goods_list;
        array_unshift($result['class_list'], ['stc_id' => "taozhuang", 'stc_name' => '优惠']);
        array_unshift($result['class_list'], ['stc_id' => "xianshi", 'stc_name' => '折扣']);
        array_unshift($result['class_list'], ['stc_id' => "hot", 'stc_name' => '热销']);
        $result['cart']['nums']   = BModel::getCount('cart', ['store_id' => $store_id]);
        $result['cart']['amount'] = BModel::getSum('cart', ['store_id' => $store_id], 'goods_price');
        return Base::jsonReturn(200, '获取成功', $result);
    }

    /**添加购物车
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

        if (!$member_id || !$store_id || !$stc_id || !$goods_id || !$quantity) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        //判断是否为特殊商品
        $storeid = BModel::getTableValue('store', ['member_id' => $member_id], 'store_id');
        if (!$storeid) {
            $storeid = 0;
        }
        if ($stc_id == 'taozhuang') {
            $bl_info = BModel::getTableFirstData('p_bundling', array('bl_id' => $goods_id));
            if (empty($bl_info) || $bl_info->bl_state == '0') {
                return Base::jsonReturn(1001, '该优惠套装已不存在，建议您单独购买');
            }

            //检查每个商品是否符合条件,并重新计算套装总价
            $bl_goods_list  = BModel::getTableAllData('p_bundling_goods', array('bl_id' => $goods_id));
            $goods_id_array = array();
            $bl_amount      = 0;
            foreach ($bl_goods_list as $goods) {
                $goods_id_array[] = $goods->goods_id;
                $bl_amount        += $goods->bl_goods_price;
            }

            $goods_list = Member::getGoodsOnlineListAndPromotionByIdArray($goods_id_array);
            foreach ($goods_list as $goods_info) {
                if (empty($goods_info)) {
                    return Base::jsonReturn(1001, '商品不存在');
                }
                if ($goods_info->store_id == $storeid) {
                    return Base::jsonReturn(1002, '不能购买自己的商品');
                }
                if (intval($goods_info->goods_storage) < 1) {
                    return Base::jsonReturn(1003, '没有库存');
                }
                if (intval($goods_info->goods_storage) < $quantity) {
                    return Base::jsonReturn(1004, '库存不足');
                }
            }

            //优惠套装作为一条记录插入购物车，图片取套装内的第一个商品图
            $goods_info                = array();
            $goods_info['store_id']    = $bl_info->store_id;
            $goods_info['goods_id']    = $goods_list[0]->goods_id;
            $goods_info['goods_name']  = $bl_info->bl_name;
            $goods_info['goods_price'] = $bl_amount;
            $goods_info['goods_num']   = $quantity;
            $goods_info['goods_image'] = $goods_list[0]->goods_image;
            $goods_info['store_name']  = $bl_info->store_name;
            $goods_info['bl_id']       = $goods_id;
            $goods_info['buyer_id']    = $member_id;

            DB::transaction(function () use ($goods_info, $goods_id_array, $quantity) {
                BModel::insertData('cart', $goods_info);
                foreach ($goods_id_array as $goodsid) {
                    $is_much = BModel::getTableValue('goods', ['goods_id' => $goodsid], 'is_much');
                    if ($is_much == 1) {
                        BModel::numDecrement('goods', ['goods_id' => $goodsid], 'goods_storage', $quantity);
                    }
                }
            });
        } elseif ($stc_id == 'xianshi') {
            $xianshi_info = BModel::getTableFirstData('p_xianshi', array('xianshi_id' => $goods_id));
            if (empty($xianshi_info) || $xianshi_info->state == '0' || $xianshi_info->end_time <= time()) {
                return Base::jsonReturn(1001, '该限时优惠已不存在');
            }
            if ($quantity < $xianshi_info->lower_limit) {
                return Base::jsonReturn(1002, '数量不能低于购买下限的数量');
            }
            //检查每个商品是否符合条件,并重新计算套装总价
            $xianshi_goods_list = BModel::getTableAllData('p_xianshi_goods', array('xianshi_id' => $goods_id));
            $goods_id_array     = array();
            $xianshi_amount     = 0;
            foreach ($xianshi_goods_list as $goods) {
                $goods_id_array[] = $goods->goods_id;
                $xianshi_amount   += $goods->xianshi_price;
            }
            $goods_list = Member::getGoodsOnlineListAndPromotionByIdArray($goods_id_array);
            foreach ($goods_list as $goods_info) {
                if (empty($goods_info)) {
                    return Base::jsonReturn(1001, '商品不存在');
                }
                if ($goods_info->store_id == $storeid) {
                    return Base::jsonReturn(1002, '不能购买自己的商品');
                }
                if (intval($goods_info->goods_storage) < 1) {
                    return Base::jsonReturn(1003, '没有库存');
                }
                if (intval($goods_info->goods_storage) < $quantity) {
                    return Base::jsonReturn(1004, '库存不足');
                }
            }
            $goods_info                = array();
            $goods_info['store_id']    = $xianshi_info->store_id;
            $goods_info['goods_id']    = $goods_list[0]->goods_id;
            $goods_info['goods_name']  = $xianshi_info->xianshi_name;
            $goods_info['goods_price'] = $xianshi_amount;
            $goods_info['goods_num']   = $quantity;
            $goods_info['goods_image'] = $goods_list[0]->goods_image;
            $goods_info['store_name']  = $xianshi_info->store_name;
            $goods_info['buyer_id']    = $member_id;

            DB::transaction(function () use ($goods_info, $goods_id_array, $quantity) {
                BModel::insertData('cart', $goods_info);
                foreach ($goods_id_array as $goodsid) {
                    $is_much = BModel::getTableValue('goods', ['goods_id' => $goodsid], 'is_much');
                    if ($is_much == 1) {
                        BModel::numDecrement('goods', ['goods_id' => $goodsid], 'goods_storage', $quantity);
                    }
                }
            });
        } else {
            $goods_info = BModel::getTableFirstData('goods', array('goods_id' => $goods_id));
            if (empty($goods_info) || $goods_info->goods_state != 1) {
                return Base::jsonReturn(1001, '商品不存在或已下架');
            }
            if ($goods_info->is_much != 2 && $goods_info->goods_storage < $quantity) {
                return Base::jsonReturn(1002, '商品库存不足');
            }
            //不能购买自己店铺
            if ($goods_info->store_id == $storeid) {
                return Base::jsonReturn(1003, '不能购买自己的商品');
            }
            $goodsinfo                = array();
            $goodsinfo['store_id']    = $goods_info->store_id;
            $goodsinfo['goods_id']    = $goods_id;
            $goodsinfo['goods_name']  = $goods_info->goods_name;
            $goodsinfo['goods_price'] = $goods_info->goods_price;
            $goodsinfo['goods_num']   = $quantity;
            $goodsinfo['goods_image'] = $goods_info->goods_image;
            $goodsinfo['store_name']  = $goods_info->store_name;
            $goodsinfo['buyer_id']    = $member_id;

            DB::transaction(function () use ($goodsinfo, $goods_id, $quantity) {
                BModel::insertData('cart', $goodsinfo);

                $is_much = BModel::getTableValue('goods', ['goods_id' => $goods_id], 'is_much');
                if ($is_much == 1) {
                    BModel::numDecrement('goods', ['goods_id' => $goods_id], 'goods_storage', $quantity);
                }

            });
        }
        $data           = [];
        $data['nums']   = BModel::getCount('cart', ['store_id' => $store_id]);
        $data['amount'] = BModel::getSum('cart', ['store_id' => $store_id], 'goods_price');
        return Base::jsonReturn(200, '添加成功', $data);
    }

    /**店铺代金券
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function voucherList(Request $request)
    {
        $store_id  = $request->input('store_id');
        $member_id = $request->input('member_id');
        if (!$store_id || !$member_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $result           = [];
        $voucher_template = BModel::getTableAllData('voucher_template', ['voucher_t_store_id' => $store_id, 'voucher_t_state' => 1]);
        if (!$voucher_template->isEmpty()) {
            foreach ($voucher_template as $k => $v) {
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function getVoucher(Request $request)
    {
        $voucher_t_id = $request->input('voucher_t_id');
        $member_id    = $request->input('member_id');
        if (!$voucher_t_id || !$member_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $voucher_info = BModel::getTableFirstData('voucher_template', ['voucher_t_id' => $voucher_t_id]);
        if (!$voucher_info) {
            return Base::jsonReturn(1001, '代金券不存在');
        }
        $member_name = BModel::getTableValue('member', ['member_id' => $member_id], 'member_name');
        $ins_data    = array(
            'voucher_code' => $voucher_info->voucher_t_id,
            'voucher_t_id' => $voucher_info->voucher_t_id,
            'voucher_title' => $voucher_info->voucher_t_title,
            'voucher_desc' => $voucher_info->voucher_t_desc,
            'voucher_start_date' => $voucher_info->voucher_t_start_date,
            'voucher_end_date' => $voucher_info->voucher_t_end_date,
            'voucher_price' => $voucher_info->voucher_t_price,
            'voucher_limit' => $voucher_info->voucher_t_limit,
            'voucher_store_id' => $voucher_info->voucher_t_store_id,
            'voucher_state' => 1,
            'voucher_active_date' => $voucher_info->voucher_t_add_date,
            'voucher_type' => 1,
            'voucher_owner_id' => $member_id,
            'voucher_owner_name' => $member_name
        );
        $res         = BModel::insertData('voucher', $ins_data);
        if ($res) {
            return Base::jsonReturn(200, '领取成功');
        } else {
            return Base::jsonReturn(2000, '领取失败');
        }
    }

    /**商品详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function goodsDetail(Request $request)
    {
        $store_id  = $request->input('store_id');
        $member_id = $request->input('member_id');
        $goods_id  = $request->input('goods_id');
        if (!$store_id || !$member_id || !$goods_id) {
            return Base::jsonReturn(1000, '参数缺失');
        }
        $data                   = [];
        $goods_field            = ['a.goods_id', 'a.goods_name', 'a.goods_salenum', 'a.goods_price', 'a.goods_marketprice', 'b.goods_body as describe'];
        $data['goods_info']     = BModel::getLeftData('goods as a', 'goods_common as b', 'a.goods_commonid', 'b.goods_commonid', ['a.goods_id' => $goods_id], $goods_field)->first();
        $com_field              = ['b.member_name', 'b.member_avatar', 'a.geval_content', 'a.geval_addtime'];
        $com_info               = Member::getGoodsComData(['geval_goodsid' => $goods_id], $com_field);
        $data['com_info']       = $com_info->isEmpty() ? [] : $com_info->toArray();
        $data['cart']['nums']   = BModel::getCount('cart', ['store_id' => $store_id]);
        $data['cart']['amount'] = BModel::getSum('cart', ['store_id' => $store_id], 'goods_price');
        $count                  = BModel::getCount('favorites', ['member_id' => $member_id, 'fav_type' => 'store', 'store_id' => $store_id]);
        $data['is_collect']     = $count == 1 ? true : false;
        return Base::jsonReturn(2000, '请求失败', $data);
    }

    /**评论店铺
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function storeCom(Request $request)
    {
        $store_id  = $request->input('store_id');
        $member_id = $request->input('member_id');
        $content   = $request->input('content');
        $kouwei    = $request->input('kouwei');
        $baozhuang = $request->input('baozhuang');
        $images    = $request->input('images');
        $images    = [1, 2, 3];
        $images    = implode(',', $images);
        $ins_data  = array(
            'content' => $content,
            'member_id' => $member_id,
            'kouwei' => $kouwei,
            'baozhuang' => $baozhuang,
            'add_time' => time(),
            'store_id' => $store_id,
            'images' => $images
        );
        $res       = BModel::insertData('store_com', $ins_data);
        if ($res) {
            return Base::jsonReturn(200, '领取成功');
        } else {
            return Base::jsonReturn(2000, '领取失败');
        }
    }

    /**店铺评论列表
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
    function myCart(Request $request)
    {
        $store_id            = $request->input('store_id');
        $member_id            = $request->input('member_id');
        $field=['a.*','b.goods_state','b.is_much','b.goods_storage'];
        $cart=BModel::getLeftData('cart as a','goods as b','a.goods_id','b.goods_id',['a.buyer_id'=>$member_id],$field);
        if($cart->isEmpty())
        {
            return [];
        }

        if($cart->toArray())
        {

            foreach($cart->toArray() as $k=>$v)
            {
                if($v->bl_id != 0)
                {
                    $result[$v->store_id][]=BModel::getTableAllData('p_bundling_goods',['bl_id'=>$v->bl_id],['goods_name','goods_image','bl_goods_price'])->toArray();
                }else{
                    $result[$v->store_id][]=$v;
                }

            }

        }
dd($result);
die;

        $storeid = BModel::getTableValue('store', ['member_id' => $member_id], 'store_id');
        $result=[];
        $store_id_array=[];
        foreach ($cart as $v)
        {
            if (empty($v) || $v->goods_state != 1) {
                return Base::jsonReturn(1001, '商品不存在或已下架');
            }
            if ($v->is_much != 2 && $v->goods_storage < $v->goods_num) {
                return Base::jsonReturn(1002, '商品库存不足');
            }
            //不能购买自己店铺
            if ($v->store_id == $storeid) {
                return Base::jsonReturn(1003, '不能购买自己的商品');
            }
            array_push($store_id_array,$v->store_id);
        }
        $store_id_array=array_unique($store_id_array);
        if(!empty($store_id_array))
        {
            $field=['store_name','goods_id','goods_name','goods_price','goods_num','goods_image','bl_id'];
            foreach ($store_id_array as $k=>$id)
            {
                $cart_data[]=BModel::getTableAllOrderData('cart',['store_id'=>$id],'cart_id')->toArray();
            }
            dd($cart_data);
        }

        return $result;



    }


}
