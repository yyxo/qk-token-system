<?php

namespace App\Console\Commands;

use App\Service\TokenRechargeService;
use Illuminate\Console\Command;


class autoRecharge extends Command{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autoRecharge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动充值';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }


    /**
     * 自动充值
     * @throws \Exception
     */
    public function handle(){
        //通证自动充值
        (new TokenRechargeService())->tokenCharge();
    }
}
