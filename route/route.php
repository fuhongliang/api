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
    'member_login'  => 'v1/Member/login',
    'get_neworder'  => 'v1/Order/newOrder',
    'refuse_order'  => 'v1/Order/refuseOrder',        //获取团体基本数据
    'order_list'  => 'v1/Order/getOrderList',
    'add_goods_class'  => 'v1/Store/addStoreGoodsClass',

]);


//
//Route::post('member_login','v1/Member/login');//商家登陆
//
//
//Route::post('get_neworder','v1/Order/newOrder');//获取新订单
//
////Route::get('refuse_order','v1/Order/refuseOrder');//拒单
//
//
//Route::get('order_list','v1/Order/getOrderList');//根据状态获取订单列表
//
//
//Route::get('add_goods_class','v1/Store/addStoreGoodsClass');//店铺添加商品分类
//
//































return [

];
