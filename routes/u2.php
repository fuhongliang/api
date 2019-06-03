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

Route::post('sms_login', 'BaseController@smsLogin');//验证码登录
Route::group(['namespace' => 'U2'], function () {

    Route::post('user_login', 'MemberController@userLogin');//账号密码登录
    Route::post('get_sms', 'MemberController@getSMS');//获取验证码

});

Route::group(['namespace' => 'U2'], function () {





});
