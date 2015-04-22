<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/21/15
 * Time: 1:12 PM
 */

namespace App;


use Illuminate\Support\Facades\File;

trait LocalDirectoryHelper {

    protected $local_destination_root;
    protected $root_path;



    public function getLocalComparesDir()
    {
        return $this->getLocalDestinationRoot() . '/compares/';
    }

    public function getLocalUploadDir()
    {
        return $this->getLocalDestinationRoot() . '/uploads/';
    }

    public function getLocalPDFsToPages($set = 'a')
    {
        return $this->getLocalDestinationRoot() . '/diffs/pdf' . $set . '_to_pages';
    }

    public function setLocalDestinationRoot($path)
    {
        if(!File::exists(storage_path($path) . '/uploads'))
            File::makeDirectory(storage_path($path . '/uploads'), $mode = 0755, $recursive = true);

        if(!File::exists(storage_path($path) . '/compares'))
            File::makeDirectory(storage_path($path . '/compares'), $mode = 0755, $recursive = true);

        $this->local_destination_root = storage_path($path);
    }

    /**
     * @return mixed
     */
    public function getLocalDestinationRoot()
    {
        return $this->local_destination_root;
    }


    /**
     * Works with UploadHelper trait
     */
    private function figureOutRootPath()
    {
        $this->setRootPath(storage_path($this->getDiffsRequestFolder()));
    }

    public function setRootPath($root_path)
    {
        $this->root_path = $root_path;
    }


    /**
     * @return mixed
     */
    public function getRootPath()
    {
        return $this->root_path;
    }

}