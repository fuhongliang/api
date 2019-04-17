<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



class SwooleController
{
    public $connections;

    public function onOpen($server, $request){
        $data=$request->get;//获取请求的参数 数组格式(array)
        //1.首先验证身份（是否通过)
        $fd=$request->fd;//正在连接的fd
        global $connections;
        $con_info=array(
            'fd'=>$fd,
            'from'=>'admin',
            'type'=>1000,//1000登入,1001登出，
            'msg'=>$fd."登入",
        );
        $userinfo=array(
            'fd'=>$fd,
            'uuid'=>time()
           );
        $connections[$fd]=$userinfo;
        $online_list=array(
            'from'=>'admin',
            'type'=>1002,//1000登入,1001登出，1002在线列表
            'msg'=>"",
            'data'=>$connections
        );
        foreach ($connections as $val)
        {
            if($val != $fd)
            {
                $server->push($val['fd'],json_encode($con_info));
            }
            $server->push($val['fd'],json_encode($online_list));
        }
    }
    
    public function onClose($server, $fd){
        global $connections;
        unset($connections[$fd]);
        //当有人退出时,发起广播
        $con_info=array(
            'fd'=>$fd,
            'from'=>'admin',
            'type'=>1001,//1000登入,1001登出，
            'msg'=>$fd."登出",
        );
        $online_list=array(
            'from'=>'admin',
            'type'=>1002,//1000登入,1001登出，1002在线列表
            'msg'=>"",
            'data'=>$connections
        );
        foreach ($connections as $val)
        {
            $server->push($val['fd'],json_encode($con_info));
            $server->push($val['fd'],json_encode($online_list));
        }

    }
    public function onMessage($server, $frame){
        global $online;
        $data=json_decode($frame->data);
        if($data->type == 1)//对个人
        {
            if(in_array($data->target,$online))
            {
                $result=self::pushMsg($data->msg,1001);
                $server->push($data->target, json_encode($result));
            }
        }
    }
    public function onRequest($request, $response){
        echo "这是request";
    }


}
