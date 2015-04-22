<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UploadWorker extends Command
{

    protected $name = 'ironworker:upload';
    protected $description = 'Upload iron worker.';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        $worker_name = $this->option('worker_name');
        $worker_file_name = $this->option('exec_worker_file_name');

        $token = env("IRON_TOKEN");
        $project_id = env("IRON_PROJECT_ID");

        $worker = new \IronWorker(array(
            'token' => $token,
            'project_id' => $project_id
        ));

        $this->info("Starting to upload \"$worker_name\" worker" . PHP_EOL);
        @$worker->upload(getcwd(), "workers/".$worker_file_name, $worker_name, array("stack" => 'php-5.5'));
    }

    protected function getOptions()
    {
        return array(
            array('worker_name', null, InputOption::VALUE_REQUIRED, 'Worker name.', null),
            array('exec_worker_file_name', null, InputOption::VALUE_REQUIRED, 'Execute worker file name.', null),
        );
    }

}