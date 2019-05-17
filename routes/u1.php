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

});
