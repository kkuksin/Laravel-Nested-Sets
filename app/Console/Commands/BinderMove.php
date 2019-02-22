<?php

namespace App\Console\Commands;

use App\Model\Binder;
use App\Services\NestedSet;
use App\Services\Timer;
use Illuminate\Console\Command;

class BinderMove extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "binder:move {--".NestedSet::PARAM_ID ."=}
                                        {--".NestedSet::PARAM_PARENT_ID ."=}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The command move binder';

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
            $nestedSet->move($options[NestedSet::PARAM_ID], $options[NestedSet::PARAM_PARENT_ID]);
        }catch(\Exception $e){
            $this->info($e->getMessage());
        }

        $this->info(Timer::end());
    }
}
