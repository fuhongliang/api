<?php
namespace app\v1\controller;

class Base
{

    /**
     * Base constructor.
     */
    function __construct()
    {

    }

    /**
     * @return array 返回json数组
     */
    public static function jsonReturn($code=200,$data=[],$msg='')
    {
        return ['code'=>$code,'data'=>$data,'msg'=>$msg];
    }

}
