<?php

namespace App\Http\Controllers\WebIM;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    function  index()
    {
        return view('webim.login');
    }

    function  checkLogin(Request $request)
    {
        $username=$request->input('username');
        $passwd=$request->input('passwd');
        //1.如果成功，连接socket
        $userinfo=array(
            'uuid'=>time()
        );
        $data=DB::table('stores')->get(['store_name','store_id']);
        return view('webim.index',compact('userinfo','data'));
        //2.如果失败，重新登录
    }
    function chatWin(Request $request,$store_id)
    {
        $data=DB::table('stores')->where(['store_id'=>$store_id])->first();
        return view('webim.chat',compact('data'));
    }
}
