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


]);





























return [

];
