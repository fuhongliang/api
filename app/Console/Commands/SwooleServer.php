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
        $this->server = new \swoole_websocket_server("0.0.0.0", 9501);
        $handler = new SwooleController();
        $this->server->on('open', array($handler,'onOpen'));
        $this->server->on('message', array($handler, 'onMessage'));
        $this->server->on('request', array($handler, 'onRequest'));
        $this->server->on('close', array($handler, 'onClose'));
        $this->server->start();
    }
}
