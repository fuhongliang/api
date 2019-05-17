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
    Route::any('add_cart','MemberController@addCart');//添加购物车

});
