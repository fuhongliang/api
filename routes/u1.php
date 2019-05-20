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
    Route::any('index','MemberController@homePage');//首页
    Route::any('sms_login','MemberController@smsLogin');//验证码登录
    Route::any('user_login','MemberController@userLogin');//账号密码登录
    Route::any('user_addpwd','MemberController@userAddPwd');//用户添加密码

    Route::any('user_address_list','MemberController@userAddrList');//用户收货地址列表
    Route::any('user_address_add','MemberController@userAddrAdd');//用户收货地址添加


    Route::any('store_info','MemberController@storeInfo');//店铺详情
    Route::any('add_cart','MemberController@addCart');//添加购物车   如果存在数量+1
    Route::any('voucher_list','MemberController@voucherList');//店铺代金券列表
    Route::any('get_voucher','MemberController@getVoucher');//领取代金券


    Route::any('goods_detail','MemberController@goodsDetail');//商品详情

    Route::any('store_com','MemberController@storeCom');//评论商家店铺

    Route::any('store_com_list','MemberController@storeComList');//店铺评论列表

    Route::any('store_detail','MemberController@storeDetail');//店铺信息详情

    Route::any('my_cart','MemberController@myCart');//我的购物车


});
