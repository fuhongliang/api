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
        echo "这是onMessage";
        //$server->push($frame->fd, "this is server");
    }
    public function onRequest($request, $response){
        echo "这是request";
    }

    //////
    static function oMsg($target_type,$username,$type)
    {
        return array(
            'target_type'=>$target_type,//target_type	发送的目标类型；users：给用户发消息，chatgroups：给群发消息，chatrooms：给聊天室发消息
            'username'=>$username,
            'msg'=>$type==1?"上线":"下线",
            'type'=>'txt',
            'from'=>'admin'
            //target	发送的目标；注意这里需要用数组，数组长度建议不大于20，即使只有一个用户，也要用数组 ['u1']；给用户发送时数组元素是用户名，给群组发送时，数组元素是groupid
            //msg	消息内容
            //type	消息类型；txt:文本消息，img：图片消息，loc：位置消息，audio：语音消息，video：视频消息，file：文件消息
            //from	表示消息发送者;无此字段Server会默认设置为“from”:“admin”，有from字段但值为空串(“”)时请求失败
            );
    }

}
