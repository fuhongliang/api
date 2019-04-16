<?php

namespace App\Console\Commands;

use App\Http\Controllers\SwooleController;
use Illuminate\Console\Command;

class SwooleServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole{action=start}';
    public $server;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $action=$this->argument('action');
        switch ($action){
            case 'start':
                $this->info("swoole observer started");
                $this->start();
                break;
            case 'stop':
                $this->info("stoped");
                break;
            case 'restart':
                $this->info("restarted");
                break;
            default:
                $this->error("unknown command");
        }
    }
    function start(){
        $serv = new \swoole_websocket_server("0.0.0.0", 9501);

//监听WebSocket连接打开事件
        $serv->on('open', function($server, $req) {
            global $users;
            global $reqs;
            $reqs[]=$req->fd;
            $users[]=array('qq'=>time(),'fd'=>$req->fd);//记录qq号
            echo "客户端: ".$req->fd."上线\n";

            foreach($reqs as $fd){
                $server->push($fd, $users);
            }

        });

        $serv->on('message', function($server, $frame) {
            global $reqs;
            echo "客户端".$frame->fd."说: ".$frame->data."\n";
            foreach($reqs as $fd){
                $server->push($fd, $frame->data);
            }
        });

        $serv->on('close', function($server, $fd) {
            echo "connection close: ".$fd."\n";
        });

        $serv->start();
    }
}
