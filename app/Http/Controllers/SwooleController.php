<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



class SwooleController
{
    static function checkToken($token)
    {
        return false;
    }

    public function onOpen($server, $request){
        $token=$request->get;//获取请求的参数 数组格式
        if(!self::checkToken($token))
        {
            $this->onClose($server,$request->fd);
        }else{
            echo "已连接上";
        }
    }
    
    public function onClose($server, $fd){
        //添加警报
        echo $fd."断开连接通道";
    }

    public function onMessage($server, $frame){
        echo "这是onMessage";
        //$server->push($frame->fd, "this is server");
    }
    public function onRequest($request, $response){
        echo "这是request";
    }

}
