<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



class SwooleController
{
    public $online;


    public function onOpen($server, $request){
        $data=$request->get;//获取请求的参数 数组格式(array)
        //1.首先验证身份（是否通过
        dd($request);


    }
    
    public function onClose($server, $fd){
        global $online;
        if(!empty($online))
        {
            unset($online[$fd]);
            $push_data=self::oflineMsg('users','zhangsan');
            foreach($online as $fds)
            {
                $server->send($fds, json_encode($push_data));
            }
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
