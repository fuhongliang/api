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

Route::group(['namespace'=>'V3'],function(){
    Route::post('member_login','MemberController@login');
    Route::any('store_jingying/{store_id}','StoreController@storeJingYingData');//店铺经营
    Route::any('get_echarts','StoreController@getEcharts');
    Route::any('get_echarts_','StoreController@getEcharts_');
});

Route::group(['namespace'=>'V3','middleware'=>['checktoken']],function(){
    Route::post('get_neworder','OrderController@getNewOrder');//获取新订单
    Route::post('refuse_order','OrderController@refuseOrder');//拒单
    Route::post('receive_order','OrderController@receiveOrder');//接单
    Route::post('order_list','OrderController@getOrderList');//根据状态获取订单列表
    Route::post('add_goods_class','StoreController@addStoreGoodsClass');//店铺添加商品分类
    Route::post('del_goods_class','StoreController@delStoreGoodsClass');//店铺删除商品分类
    Route::post('goods_class_list','StoreController@storeGoodsClassList');//店铺商品分类列表
    Route::post('sort_goods_class','StoreController@sortStoreGoodsClass');//店铺商品分类排序
    Route::post('goods_list','GoodsController@storeGoodsList');//店铺商品列表-----
    Route::post('chgoods_state','GoodsController@changeGoodsState');//商品上下架
    Route::post('add_goods','GoodsController@addGoods');//新建商品-----
    Route::post('del_goods','GoodsController@delGoods');//删除商品-----
    Route::post('store_setting','StoreController@getStoreSetting');//门店设置详情
    Route::post('store_set_workstate','StoreController@setWorkState');//门店设置营业状态
    Route::post('store_set_desc','StoreController@setStoreDesc');//门店设置公告
    Route::post('store_set_phone','StoreController@setStorePhone');//门店设置电话
    Route::post('store_set_worktime','StoreController@setStoreWorkTime');//门店设置营业时间
    Route::post('store_msg_feedback','StoreController@msgFeedBack');//意见反馈
    Route::post('get_sms','StoreController@getSMS');//获取验证码
    Route::post('edit_passwd','StoreController@editPasswd');//修改密码
    Route::post('get_store_com','StoreController@getStoreCom');//获取店铺评论
    Route::post('store_feedback','StoreController@storeFeedback');//店铺回复
    Route::any('store_yunying','StoreController@storeYunYingInfo');//店铺运营
    Route::post('edit_goods','GoodsController@editGoods');//编辑商品
    Route::post('goods_info','GoodsController@getGoodsInfo');//商品详情
    Route::post('mianzhi_list','VoucherController@mianzhiList');//面值列表
    Route::post('voucher_edit','VoucherController@voucherEdit');//添加/删除代金券
    Route::post('voucher_list','VoucherController@voucherList');//代金券列表
    Route::post('voucher_info','VoucherController@voucherInfo');//代金券详情
    Route::post('voucher_del','VoucherController@voucherDel');//代金券删除
    Route::post('bundling_edit','VoucherController@bundlingEdit');//添/编辑优惠套装
    Route::post('bundling_list','VoucherController@bundlingList');//优惠套装列表
    Route::post('bundling_del','VoucherController@bundlingDel');//优惠套装删除
    Route::post('bundling_info','VoucherController@bundlingInfo');//优惠套装详情
    Route::post('mamsong_edit','VoucherController@mamsongEdit');//添加/编辑满送
    Route::post('mamsong_list','VoucherController@mamsongList');//满送列表
    Route::post('mamsong_del','VoucherController@mansongDel');//满送删除
    Route::post('xianshi_edit','VoucherController@xianshiEdit');//添加/编辑折扣
    Route::post('xianshi_list','VoucherController@xianshiList');//折扣列表
    Route::post('xianshi_goods_list','GoodsController@xianshiGoodsList');//折扣商品列表
    Route::post('xianshi_del','VoucherController@xianshiDel');//折扣删除
    Route::post('xianshi_info','VoucherController@xianshiInfo');//折扣详情
    Route::post('image_upload/{type}','GoodsController@upImage');//文件上传
    Route::post('check_mobile','MemberController@checkMobile');//商家注册检测手机号
    Route::post('member_register','MemberController@memberRegister');//商家注册
});
