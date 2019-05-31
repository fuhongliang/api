<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['namespace' => 'U1'], function () {
    Route::post('sms_login', 'MemberController@smsLogin');//验证码登录
    Route::post('user_login', 'MemberController@userLogin');//账号密码登录
    Route::post('get_sms', 'MemberController@getSMS');//获取验证码
    Route::post('home_page', 'MemberController@homePage');//首页
    Route::post('store_info', 'MemberController@storeInfo');//店铺详情
    Route::post('area_list', 'MemberController@areaList');//地区列表
    Route::post('all_comment', 'MemberController@allComment');//店铺评价
    Route::post('storeinfo', 'MemberController@storDetail');//店铺详情
    Route::post('goods_detail', 'MemberController@goodsDetail');//商品详情
});

Route::group(['namespace' => 'U1', 'middleware' => ['checktoken']], function () {
    Route::post('user_add_pwd', 'MemberController@userAddPwd');//添加密码
    Route::post('user_address_list', 'MemberController@userAddrList');//用户收货地址列表
    Route::post('user_address_info', 'MemberController@userAddrInfo');//用户收货地址详情
    Route::post('user_address_save', 'MemberController@userAddrSave');//用户收货地址保存
    Route::post('user_address_del', 'MemberController@userAddrDel');//用户收货地址删除
    Route::post('voucher_list', 'MemberController@voucherList');//店铺代金券列表
    Route::post('get_voucher', 'MemberController@getVoucher');//领取代金券
    Route::post('add_cart', 'MemberController@addCart');//添加购物车
    Route::post('my_cart', 'MemberController@myCart');//我的购物车
    Route::post('clear_cart', 'MemberController@clearCart');//清购物车

    Route::post('store_com', 'MemberController@storeCom');//去评价---
    Route::post('cart_detail', 'MemberController@cartDetail');//购物车详情-----
    Route::post('go_settlement', 'MemberController@Settlement');//去结算
    Route::post('buy_step', 'MemberController@buyStep');//下单
    Route::post('order_list', 'MemberController@orderList');//订单列表
    Route::post('order_info', 'MemberController@orderInfo');//订单详情






});
