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


Route::rule('hook','v1/Base/hook','GET|POST');//自动部署
/**
 * 后台接口
*/

Route::group('v1/', [
    'member_login'=>'/Member/login'//商家登陆
])->prefix('v1');


Route::group('v1/', [
    'get_neworder'  => '/Order/newOrder',//获取新订单

    'refuse_order'  => '/Order/refuseOrder',    //拒单

    'receive_order'=>'/Order/receiveOrder',    //接单

    'order_list'  => '/Order/getOrderList',//根据状态获取订单列表

    'add_goods_class'  => '/Store/addStoreGoodsClass',//店铺添加商品分类

    'del_goods_class'  => '/Store/delStoreGoodsClass',//店铺删除商品分类

    'goods_class_list'  => '/Store/storeGoodsClassList',//店铺商品分类列表

    'sort_goods_class'  => '/Store/sortStoreGoodsClass',//店铺商品分类排序

    'goods_list'  => '/Store/storeGoodsList',//店铺商品列表

    'add_goods'=>'/Goods/addGoods',//添加商品

    'del_goods'=>'/Goods/delGoods',//删除商品

    'store_setting'=>'/Store/getStoreSetting',//门店设置详情

    'store_set_workstate'=>'/Store/setWorkState',//门店设置营业状态

    'store_set_desc'=>'/Store/setStoreDesc',//门店设置公告

    'store_set_phone'=>'/Store/setStorePhone',//门店设置电话

    'store_set_worktime'=>'/Store/setStoreWorkTime',//门店设置营业时间

    'store_msg_feedback'=>'/Store/msgFeedBack',//意见反馈

    'get_sms'=>'/Store/getSMS',//获取验证码

    'edit_passwd'=>'/Store/editPasswd',//修改密码

    'get_store_com'=>'/Store/getStoreCom',//获取店铺评论

    'store_feedback'=>'/Store/storeFeedback',//店铺回复

    'store_yunying'=>'/Store/storeYunYingInfo',//店铺运营

    'store_jingying'=>'/Store/storeJingYingData',//店铺经营

    'get_echarts'=>'/Store/getEcharts',

    'get_echarts_'=>'/Store/getEcharts_',

])->middleware('checkToken')->prefix('v1');





Route::group('v2/', [
    'goods_list'  => '/Store/storeGoodsList',//店铺商品列表

])->middleware('checkToken')->prefix('v2');























return [

];
