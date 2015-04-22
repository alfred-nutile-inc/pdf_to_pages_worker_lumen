<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 2/12/15
 * Time: 7:59 PM
 */

namespace App;


use Illuminate\Support\Facades\File;

trait UploadHelper {

    protected $project_id;
    protected $request_uuid;
    protected $destinationDir;
    protected $diff_request;
    protected $base_path;


    public function getDiffsRequestFolder()
    {
        return 'bundles/' . $this->project_id . '/requests/' . $this->getRequestUuid();
    }

    public function getBundleRequestFolder()
    {
        return 'bundles/' . $this->project_id . '/requests/' . $this->getRequestUuid() . '/uploads/';
    }

    public function getBundleRequestOriginalsFolder($project_id = false, $request_id = false)
    {
        if($project_id == false)
            $project_id = $this->getProjectId();

        if($request_id == false)
            $request_id = $this->getRequestUuid();

        return 'bundles/' . $project_id . '/requests/' . $request_id . '/originals/';
    }

    public function getBundleRequestCompareFolder()
    {
        return 'bundles/' . $this->getProjectId() . '/requests/' . $this->getRequestUuid() . '/compares/';
    }



    /**
     * @return mixed
     */
    public function getRequestUuid()
    {
        return $this->request_uuid;
    }

    protected function prepareFolder($path = false)
    {
        if($path == false)
            $path = $this->getBasePath();
        try
        {
            if(!File::exists($path))
            {
                File::makeDirectory($path, $mode = 0755, $recursive = true, $force = true);
            }
        } catch(\Exception $e)
        {
            $message = sprintf("Error making folder %s message %s", $path, $e->getMessage());
            throw new \Exception($message);
        }
    }

    /**
     * @param mixed $request_uuid
     */
    public function setRequestUuid($request_uuid)
    {
        $this->request_uuid = $request_uuid;
    }

    /**
     * @return mixed
     */
    public function getDestinationDir()
    {
        return $this->destinationDir;
    }

    /**
     * @param mixed $destinationDir
     * @return $this
     */
    public function setDestinationDir($destinationDir)
    {
        $this->destinationDir = $destinationDir;
        return $this;
    }

    public function getBasePath()
    {
        if($this->base_path == null)
            $this->setBasePath();
        return $this->base_path;
    }

    public function setBasePath($base = null)
    {
        if($base == null)
            $base = storage_path() . '/diffs/' . $this->getBundleRequestFolder();
        $this->base_path = $base;
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

}
