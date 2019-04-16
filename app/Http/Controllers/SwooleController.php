<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



class SwooleController
{
    public function onOpen($server, $fd){
        echo "建立连接通道ID：$fd\n";
    }
    
    public function onClose($server, $fd){
        //添加警报
        echo "断开连接通道: {$fd}\n";
    }

    public function onMessage($server, $frame){
        echo "这是onMessage";
        //$server->push($frame->fd, "this is server");
    }
    public function onRequest($request, $response){
        echo "这是request";
    }

}
