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

    'del_goods'=>'v1/Goods/delGoods',//添加商品

    'store_setting'=>'v1/Store/getStoreSetting',//门店设置详情

    'store_set_workstate'=>'v1/Store/setWorkState',//门店设置营业状态

    'store_set_desc'=>'v1/Store/setStoreDesc',//门店设置公告

    'store_set_phone'=>'v1/Store/setStorePhone',//门店设置电话

    'store_set_worktime'=>'v1/Store/setStoreWorkTime',//门店设置营业时间

]);





























return [

];
