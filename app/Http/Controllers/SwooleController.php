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
            'type'=>1//1登录
        );
        $connections[$fd]=$con_info;
        foreach ($connections as $val)
        {
            if($val['fd'] != $fd)
            {
                $server->push($val['fd'],$fd."加入");
            }
        }
    }
    
    public function onClose($server, $fd){
        global $connections;
        unset($connections[$fd]);
        //当有人退出时,发起广播
        foreach ($connections as $val)
        {
            $server->push($val['fd'],$fd."退出");
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
