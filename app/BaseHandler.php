<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/23/15
 * Time: 12:50 PM
 */

namespace App;


use AlfredNutileInc\DiffTool\DiffToolDTO;
use App\Helpers\CompareJsonHelper;
use Illuminate\Support\Facades\File;

class BaseHandler {

    use CompareJsonHelper;
    use PathHelper;
    use LocalDirectoryHelper;

    public $request_id;
    public $project_id;
    protected $set;
    protected $results = [];

    public $stage = 0;

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
     * @param int $stage
     */
    public function setStage($stage)
    {
        $this->stage = $stage;
    }

    /**
     * @return int
     */
    public function getStage()
    {
        return $this->stage;
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

    public function writeCompareFile()
    {
        $this->updateCompareValue('project_id', false, false, $this->getProjectId());
        $this->updateCompareValue('request_id', false, false, $this->getRequestId());
        $this->updateCompareValue('stage', false, false, $this->getStage());
        File::put($this->getLocalDestinationRoot() . '/compares/compare.json', json_encode($this->getCompareJsonState(), JSON_PRETTY_PRINT));
        return sprintf("Wrote compare file to path %s", $this->getLocalDestinationRoot() . '/compares/compare.json');
    }
}