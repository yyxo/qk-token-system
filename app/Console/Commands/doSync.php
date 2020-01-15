<?php

namespace App\Console\Commands;

use App\Service\NewSyncService;
use Illuminate\Console\Command;


class doSync extends Command{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'doSync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动同步';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }


    /**
     * @throws \Exception
     */
    public function handle(){
        (new NewSyncService())->synchronizeTransactionLogs();
    }
}
