<?php
namespace app\v1\controller;
use think\Controller;
class Base extends Controller
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
    public static function jsonReturn($code=200,$data,$msg='')
    {

        return json(['code'=>$code,'data'=>empty($data) ? null :$data,'msg'=>$msg]);
    }
    /**
     * 生成token
     */
    public static function makeToken($member_name,$member_pwd)
    {
        $secret_key=md5(date('y-m-d h:i:s',time()).microtime());
        return md5($member_name.$member_pwd.$secret_key);
    }




}
