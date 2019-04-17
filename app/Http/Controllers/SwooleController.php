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
        //有人上线时，发起广播
        foreach ($connections as $client_fd)
        {
            if($fd ! == $client_fd)
            {
                $server->push($client_fd, '有人上线了');
            }
        }

    }
    
    public function onClose($server, $fd){
        global $connections;
        unset($connections[$fd]);
        //当有人退出时,发起广播
        foreach ($connections as $client_fd)
        {

            $server->push($client_fd, '有人下线了');

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

    //////
    static function onlineMsg($target_type,$username)
    {
        return array(
            'username'=>$username,
            'msg'=>"上线",
            'type'=>1,//1 发送给个人  2 全体
            'target'=>'',
            'from'=>'admin'
            );
    }
    static function oflineMsg($target_type,$username)
    {
        return array(
            'username'=>$username,
            'msg'=>"下线",
            'type'=>1,//1 发送给个人  2 全体
            'target'=>'',
            'from'=>'admin'
        );
    }
    static function pushMsg($msg,$type)
    {
        return array(
            'msg'=>$msg,
            'type'=>$type,//1000 系统消息  ， 10001 聊天消息
            'target'=>'',
            'from'=>'admin'
        );
    }
}
