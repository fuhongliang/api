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

////////
Route::group(['namespace'=>'WebIM','middleware' => 'web'],function(){
    Route::any('/','IndexController@index');
});
//////////////第一版
Route::any('test','testController@test');
Route::any('v1/member_login','V1\MemberController@login');
Route::any('v1/store_jingying/{store_id}','V1\StoreController@storeJingYingData');//店铺经营
Route::any('v1/get_echarts','V1\StoreController@getEcharts');
Route::any('v1/get_echarts_','V1\StoreController@getEcharts_');
//
Route::group(['namespace'=>'V1','prefix'=>'v1','middleware'=>['checktoken']],function(){

    Route::any('get_neworder','OrderController@getNewOrder');//获取新订单

    Route::any('refuse_order','OrderController@refuseOrder');//拒单

    Route::any('receive_order','OrderController@receiveOrder');//接单

    Route::any('order_list','OrderController@getOrderList');//根据状态获取订单列表

    Route::any('add_goods_class','StoreController@addStoreGoodsClass');//店铺添加商品分类

    Route::any('del_goods_class','StoreController@delStoreGoodsClass');//店铺删除商品分类

    Route::any('goods_class_list','StoreController@storeGoodsClassList');//店铺商品分类列表

    Route::any('sort_goods_class','StoreController@sortStoreGoodsClass');//店铺商品分类排序

    Route::any('goods_list','StoreController@storeGoodsList');//店铺商品列表

    Route::any('add_goods','GoodsController@addGoods');//添加商品

    Route::any('del_goods','GoodsController@delGoods');//删除商品

    Route::any('store_setting','StoreController@getStoreSetting');//门店设置详情

    Route::any('store_set_workstate','StoreController@setWorkState');//门店设置营业状态

    Route::any('store_set_desc','StoreController@setStoreDesc');//门店设置公告

    Route::any('store_set_phone','StoreController@setStorePhone');//门店设置电话

    Route::any('store_set_worktime','StoreController@setStoreWorkTime');//门店设置营业时间

    Route::any('store_msg_feedback','StoreController@msgFeedBack');//意见反馈

    Route::any('get_sms','StoreController@getSMS');//获取验证码

    Route::any('edit_passwd','StoreController@editPasswd');//修改密码

    Route::any('get_store_com','StoreController@getStoreCom');//获取店铺评论

    Route::any('store_feedback','StoreController@storeFeedback');//店铺回复

    Route::any('store_yunying','StoreController@storeYunYingInfo');//店铺运营


});


/////////////////第二版
Route::any('v2/member_login','V2\MemberController@login');
Route::any('v2/store_jingying/{store_id}','V2\StoreController@storeJingYingData');//店铺经营
Route::any('v2/get_echarts','V2\StoreController@getEcharts');
Route::any('v2/get_echarts_','V2\StoreController@getEcharts_');
Route::group(['namespace'=>'V2','prefix'=>'v2','middleware'=>['checktoken']],function(){
    Route::any('get_neworder','OrderController@getNewOrder');//获取新订单

    Route::any('refuse_order','OrderController@refuseOrder');//拒单

    Route::any('receive_order','OrderController@receiveOrder');//接单

    Route::any('order_list','OrderController@getOrderList');//根据状态获取订单列表

    Route::any('add_goods_class','StoreController@addStoreGoodsClass');//店铺添加商品分类

    Route::any('del_goods_class','StoreController@delStoreGoodsClass');//店铺删除商品分类

    Route::any('goods_class_list','StoreController@storeGoodsClassList');//店铺商品分类列表

    Route::any('sort_goods_class','StoreController@sortStoreGoodsClass');//店铺商品分类排序

    Route::any('goods_list','GoodsController@storeGoodsList');//店铺商品列表-----

    Route::any('chgoods_state','GoodsController@changeGoodsState');//商品上下架

    Route::any('add_goods','GoodsController@addGoods');//新建商品-----

    Route::any('del_goods','GoodsController@delGoods');//删除商品-----

    Route::any('store_setting','StoreController@getStoreSetting');//门店设置详情

    Route::any('store_set_workstate','StoreController@setWorkState');//门店设置营业状态

    Route::any('store_set_desc','StoreController@setStoreDesc');//门店设置公告

    Route::any('store_set_phone','StoreController@setStorePhone');//门店设置电话

    Route::any('store_set_worktime','StoreController@setStoreWorkTime');//门店设置营业时间

    Route::any('store_msg_feedback','StoreController@msgFeedBack');//意见反馈

    Route::any('get_sms','StoreController@getSMS');//获取验证码

    Route::any('edit_passwd','StoreController@editPasswd');//修改密码

    Route::any('get_store_com','StoreController@getStoreCom');//获取店铺评论

    Route::any('store_feedback','StoreController@storeFeedback');//店铺回复

    Route::any('store_yunying','StoreController@storeYunYingInfo');//店铺运营

    Route::any('edit_goods','GoodsController@editGoods');//编辑商品-------------

    Route::any('goods_info','GoodsController@getGoodsInfo');//商品详情-------------

    Route::any('mianzhi_list','VoucherController@mianzhiList');//面值列表------------

    Route::any('voucher_edit','VoucherController@voucherEdit');//添加/删除代金券------------

    Route::any('voucher_list','VoucherController@voucherList');//代金券列表------------

    Route::any('voucher_info','VoucherController@voucherInfo');//代金券详情------------

    Route::any('voucher_del','VoucherController@voucherDel');//代金券删除------------

    Route::any('bundling_edit','VoucherController@bundlingEdit');//添/编辑优惠套装------------

    Route::any('bundling_list','VoucherController@bundlingList');//优惠套装列表------------

    Route::any('bundling_del','VoucherController@bundlingDel');//优惠套装删除------------

    Route::any('bundling_info','VoucherController@bundlingInfo');//优惠套装详情------------

    Route::any('mamsong_edit','VoucherController@mamsongEdit');//添加/编辑满送------------

    Route::any('mamsong_list','VoucherController@mamsongList');//满送列表------------

    Route::any('mamsong_del','VoucherController@mansongDel');//满送删除------------

    Route::any('xianshi_edit','VoucherController@xianshiEdit');//添加/编辑折扣------------

    Route::any('xianshi_list','VoucherController@xianshiList');//折扣列表------------

    Route::any('xianshi_del','VoucherController@xianshiDel');//折扣删除------------

    Route::any('xianshi_info','VoucherController@xianshiInfo');//折扣详情------------

    Route::any('image_upload/{type}','GoodsController@upImage');//文件上传------------

});
