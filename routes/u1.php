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
Route::post('get_sms', 'BaseController@getSMS');//获取验证码

Route::group(['namespace' => 'U1'], function () {

    Route::post('sms_login', 'MemberController@smsLogin');//验证码登录
    Route::post('user_login', 'MemberController@userLogin');//账号密码登录
});

Route::group(['namespace' => 'U1','middleware' => ['checktoken']], function () {
    Route::post('user_add_pwd','MemberController@userAddPwd');//添加密码
    Route::post('user_address_list','MemberController@userAddrList');//用户收货地址列表
    Route::post('user_address_info','MemberController@userAddrInfo');//用户收货地址详情
    Route::post('user_address_save','MemberController@userAddrSave');//用户收货地址保存




    Route::any('index','MemberController@homePage');//首页

    Route::any('store_info','MemberController@storeInfo');//店铺详情
    Route::any('add_cart','MemberController@addCart');//添加购物车
    Route::any('voucher_list','MemberController@voucherList');//店铺代金券列表
    Route::any('get_voucher','MemberController@getVoucher');//领取代金券


    Route::any('goods_detail','MemberController@goodsDetail');//商品详情

    Route::any('store_com','MemberController@storeCom');//评论商家店铺

    Route::any('store_com_list','MemberController@storeComList');//店铺评论列表

    Route::any('store_detail','MemberController@storeDetail');//店铺信息详情

    Route::any('my_cart','MemberController@myCart');//我的购物车
    Route::any('clear_cart','MemberController@clearCart');//清购物车

    Route::any('go_settlement','MemberController@Settlement');//去结算

    Route::any('buy_step','MemberController@buyStep');//下单

    Route::any('order_list','MemberController@orderList');//订单列表

    Route::any('order_info','MemberController@orderInfo');//订单详情


});
