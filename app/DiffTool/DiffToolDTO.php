<?php namespace AlfredNutileInc\DiffTool;

class DiffToolDTO {


    public $project_id;
    public $request_id;
    public $stage;
    public $driver;

    public function __construct($project_id, $request_id, $stage, $driver = false, $set = 'a')
    {
        $this->project_id = $project_id;
        $this->request_id = $request_id;
        $this->stage = $stage;
        $this->driver = $driver;
        $this->set = $set;
    }

}
