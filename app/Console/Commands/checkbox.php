<?php

namespace App\Console\Commands;

use App\Service\BoxesService;
use Illuminate\Console\Command;

class checkbox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkbox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '打开箱子';

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
        ini_set('max_execution_time', 60);

        $end_time = time() + 55;
        while (true)
        {
            if ($end_time <= time()) {
                break;
            }
            try
            {
                (new BoxesService())->checkBoxStatus();
            }
            catch (\exception $ex)
            {
                echo $ex->getMessage() . "\n";
            }
            sleep(5);
        }
    }
}
