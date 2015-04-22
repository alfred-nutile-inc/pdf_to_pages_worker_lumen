<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/21/15
 * Time: 1:19 PM
 */

namespace App;


trait PathHelper {

    public function getDiffsRequestFolder()
    {
        return 'bundles/' . $this->project_id . '/requests/' . $this->request_id;
    }

    public function getBundleRequestRootFolder($project_id = false, $request_id = false)
    {
        if($project_id == false)
            $project_id = $this->project_id;

        if($request_id == false)
            $request_id = $this->request_id;

        return 'bundles/' . $project_id . '/requests/' . $request_id;
    }

    public function getBundleRequestRootFolderLocal($project_id = false, $request_id = false)
    {
        if($project_id == false)
            $project_id = $this->project_id;

        if($request_id == false)
            $request_id = $this->request_id;

        return storage_path($this->getBundleRequestRootFolder($project_id, $request_id));
    }

    public function getBundleRequestFolder()
    {
        return 'bundles/' . $this->project_id . '/requests/' . $this->request_id . '/uploads/';
    }

    public function getBundleRequestOriginalsFolder($project_id = false, $request_id = false)
    {
        if($project_id == false)
            $project_id = $this->project_id;

        if($request_id == false)
            $request_id = $this->request_id;

        return 'bundles/' . $project_id . '/requests/' . $request_id . '/originals/';
    }

    public function getBundleRequestCompareFolder($project_id = false, $request_id = false)
    {
        if($project_id == false)
            $project_id = $this->project_id;

        if($request_id == false)
            $request_id = $this->request_id;

        return 'bundles/' . $project_id . '/requests/' . $request_id . '/compares/';
    }
}