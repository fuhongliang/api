<?php
namespace app\v1\controller;
use app\v1\controller\Base;
use think\Request;
use app\v1\model\Goods as GoodsModel;
/**
 * Class Goods 商品
 * @package app\v1\controller
 */
class Goods extends Base
{
    public function addGoods(Request $request)
    {
        $store_id=$request->param('store_id');
        $goods_name=$request->param('goods_name');
        $goods_price=$request->param('goods_price');
        $goods_marketprice=$request->param('origin_price');
        $goods_storage=$request->param('goods_storage');

        if(!$store_id)
        {
            return Base::jsonReturn(1000,[],'参数缺失');
        }

    }










}
