<?php

namespace App\Console\Commands;

use App\Model\Binder;
use App\Services\NestedSet;
use App\Services\Timer;
use Illuminate\Console\Command;

class BinderAdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "binder:add {--".NestedSet::PARAM_NAME."=} 
                                       {--".NestedSet::PARAM_DB_ID ."=} 
                                       {--".NestedSet::PARAM_PARENT_ID."=}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The command add binder';

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
            $nestedSet->add($options);
        }catch(\Exception $e){
            $this->info($e->getMessage());
        }

        $this->info(Timer::end());
    }
}
