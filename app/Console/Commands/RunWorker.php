<?php namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RunWorker extends Command
{

    protected $name = 'ironworker:run';
    protected $description = 'Upload iron worker.';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        var_dump(Queue::configuration());
        $queue_name = $this->option('queue_name');
        $this->info(Queue::pushRaw(
            array(
                'ids' => array("1,2")
            ),
            $queue_name));
    }

    protected function getOptions()
    {
        return array(
            array('queue_name', null, InputOption::VALUE_REQUIRED, 'Queue name.', null),
        );
    }

}