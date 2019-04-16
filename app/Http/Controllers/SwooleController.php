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
            $online[$request->fd]=['fd'=>$request->fd,'uuid'=>time()];
            foreach ($server->connections as $fd) {
                if($fd != $request->fd) {
                    $server->push($fd, json_encode($online));
                }
            }
        }
    }
    
    public function onClose($server, $fd){
        global $online;
        unset($online[$fd]);
        foreach($server->connections as $fds)
        {
            if($fds != $fd)
            {
                $server->send($fd, "hello");
            }

        }
    }

    public function onMessage($server, $frame){
        echo "这是onMessage";
        //$server->push($frame->fd, "this is server");
    }
    public function onRequest($request, $response){
        echo "这是request";
    }

}
