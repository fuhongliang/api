<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SwooleController;
class swoole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole{action=start}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'you can input action as start,stop,restart';

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
                self::start();
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
   static function start(){
        $server = new \swoole_websocket_server("0.0.0.0", 2346);
        $handler = new SwooleController();
        dd($server);
        $server->on('connect', [$handler, 'onConnect']);
        $server->on('message', [$handler, 'onMessage']);
        $server->on('close', [$handler, 'onClose']);
        $server->start();
    }
}
