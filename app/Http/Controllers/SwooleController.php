<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



class SwooleController
{
    public function checkToken($token)
    {
        echo $token;
    }

    public function onOpen($server, $req){
        echo $server->data;
        echo "客户端: ".$req->fd."上线\n";
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
