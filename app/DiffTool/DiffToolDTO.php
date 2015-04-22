<?php namespace AlfredNutileInc\DiffTool;

use Illuminate\Support\Facades\Auth;

class DiffToolDTO {


    public $project_id;
    public $request_id;
    public $stage;
    public $driver;

    public function __construct(
        $project_id, $request_id, $stage, $driver = false, $set = 'a', $user_id = false)
    {
        $this->project_id = $project_id;
        $this->request_id = $request_id;
        $this->stage = $stage;
        $this->driver = $driver;
        $this->set = $set;
        $this->user_id = ($user_id == false) ? $this->get_user_id() : $user_id;
    }

    private function get_user_id()
    {
        return (!Auth::guest()) ? Auth::user()->id : 0;
    }

}
