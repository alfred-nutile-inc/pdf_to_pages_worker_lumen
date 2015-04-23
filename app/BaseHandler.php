<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/23/15
 * Time: 12:50 PM
 */

namespace App;


use AlfredNutileInc\DiffTool\DiffToolDTO;

class BaseHandler {

    public $request_id;
    public $project_id;
    protected $set;
    protected $results = [];


    /**
     * @var DiffToolDTO
     */
    protected $dto;

    public function setDto($payload)
    {
        $this->dto = $payload;
    }

    /**
     * @return DiffToolDTO
     */
    public function getDto()
    {
        return $this->dto;
    }

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->request_id;
    }

    /**
     * @param mixed $request_id
     */
    public function setRequestId($request_id)
    {
        $this->request_id = $request_id;
    }

    /**
     * @return mixed
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * @param mixed $project_id
     */
    public function setProjectId($project_id)
    {
        $this->project_id = $project_id;
    }

    /**
     * @return mixed
     */
    public function getSet()
    {
        return $this->set;
    }

    /**
     * @param mixed $set
     */
    public function setSet($set)
    {
        $this->set = $set;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param array $results
     */
    public function setResults($results)
    {
        $this->results[] = $results;
    }
}