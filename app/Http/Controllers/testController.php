<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class testController extends Controller
{
    function test()
    {
        $server = new \swoole_websocket_server("0.0.0.0", 2346);
        $server->push(1, "this is server");
    }
}
