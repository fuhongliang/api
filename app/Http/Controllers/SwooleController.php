<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



class SwooleController
{
    public $online;
    static function checkToken($data)
    {
        return true;
    }

    public function onOpen($server, $request){
        $data=$request->get;//获取请求的参数 数组格式
        if(!self::checkToken($data))
        {
            $this->onClose($server,$request->fd);
        }else{
            global $online;
            $online[$request->fd]=['fd'=>$request->fd,'uuid'=>time()];//记录所有登录的信息
            $push_data=self::onlineMsg('users','zhangsan');
            foreach ($online as $val) {
                if($val['fd'] != $request->fd) {
                    $server->push($val['fd'], json_encode($push_data));
                }
            }
        }
    }
    
    public function onClose($server, $fd){
        global $online;
        unset($online[$fd]);
        $push_data=self::oflineMsg('users','zhangsan');
        foreach($online as $fds)
        {
            $server->send($fds, json_encode($push_data));
        }
    }

    public function onMessage($server, $frame){
        $data=json_decode($frame->data);
        if($data->type == 1)//对个人
        {
            $result=self::pushMsg($data->msg,1001);
            $server->push($data->target, json_encode($result));
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
