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
            $push_data=self::oMsg('users','zhangsan',1);
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
        $push_data=self::oMsg('users','zhangsan',2);
        foreach($online as $fds)
        {
            $server->send($fds, json_encode($push_data));
        }
    }

    public function onMessage($server, $frame){
        $data=json_decode($frame->data);
        if($data->type == 1)//对个人
        {
            $server->send($data->target, 666);
        }
    }
    public function onRequest($request, $response){
        echo "这是request";
    }

    //////
    static function oMsg($target_type,$username,$type)
    {
        return array(
            'username'=>$username,
            'msg'=>$type==1?"上线":"下线",
            'type'=>1,//1 发送给个人  2 全体
            'target'=>'',
            'from'=>'admin'
            );
    }

}
