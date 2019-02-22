<?php

namespace App\Console\Commands;

use App\Model\Binder;
use App\Services\NestedSet;
use App\Services\Timer;
use Illuminate\Console\Command;

class BinderDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "binder:delete {--".NestedSet::PARAM_DB_ID ."=}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The command delete binder';

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
        //
        Timer::start();
        try{
            $options = $this->option();
            $nestedSet = new NestedSet();
            $nestedSet->delete($options[NestedSet::PARAM_DB_ID]);
        }catch(\Exception $e){
            $this->info($e->getMessage());
        }

        $this->info(Timer::end());
    }
}
