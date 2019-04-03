<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



class SwooleController
{
    public function onConnect($server, $fd){
        echo "建立连接通道ID：$fd\n";
    }
    
    public function onClose($server, $fd){
        //添加警报
        echo "断开连接通道: {$fd}\n";
    }

    public function onMessage($server, $frame){
        $server->push($frame->fd, "this is server");
    }
}
