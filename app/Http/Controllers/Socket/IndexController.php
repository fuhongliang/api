<?php

namespace App\Http\Controllers\Socket;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    function index()
    {
        return view('layout.template');
    }
    function store_list()
    {
        $list=DB::table('store')->paginate(10);
        return view('store.list',compact('list'));
    }
}
