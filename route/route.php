<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------



/**
 * 后台接口
*/
Route::group('', [
    'member_login'  => 'v1/Member/login',//商家登陆

    'get_neworder'  => 'v1/Order/newOrder',//获取新订单

    'refuse_order'  => 'v1/Order/refuseOrder',    //拒单

    'receive_order'=>'v1/Order/receiveOrder',    //接单

    'order_list'  => 'v1/Order/getOrderList',//根据状态获取订单列表

    'add_goods_class'  => 'v1/Store/addStoreGoodsClass',//店铺添加商品分类

    'del_goods_class'  => 'v1/Store/delStoreGoodsClass',//店铺删除商品分类

    'goods_class_list'  => 'v1/Store/storeGoodsClassList',//店铺商品分类列表

    'sort_goods_class'  => 'v1/Store/sortStoreGoodsClass',//店铺商品分类排序

    'goods_list'  => 'v1/Store/storeGoodsList',//店铺商品列表

    'add_goods'=>'v1/Goods/addGoods',//添加商品

    'del_goods'=>'v1/Goods/delGoods',//删除商品

    'store_setting'=>'v1/Store/getStoreSetting',//门店设置详情

    'store_set_workstate'=>'v1/Store/setWorkState',//门店设置营业状态

    'store_set_desc'=>'v1/Store/setStoreDesc',//门店设置公告

    'store_set_phone'=>'v1/Store/setStorePhone',//门店设置电话

    'store_set_worktime'=>'v1/Store/setStoreWorkTime',//门店设置营业时间

    'store_msg_feedback'=>'v1/Store/msgFeedBack',//意见反馈

    'get_sms'=>'v1/Store/getSMS',//获取验证码

    'edit_passwd'=>'v1/Store/editPasswd',//修改密码

    'get_store_com'=>'v1/Store/getStoreCom',//获取店铺评论

    'store_feedback'=>'v1/Store/storeFeedback',//店铺回复

    'store_yunying'=>'v1/Store/storeYunYingInfo',//店铺运营

    //'store_jingying'=>'v1/Store/storeJingYingData',//店铺经营
//
//    'get_echarts'=>'v1/Store/getEcharts',
//
//    'get_echarts_'=>'v1/Store/getEcharts_',

]);
Route::get('store_jingying','v1/Store/storeJingYingData');

Route::get('get_echarts','v1/Store/getEcharts');
Route::get('get_echarts_','v1/Store/getEcharts_');



























return [

];
