<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/21/15
 * Time: 11:18 AM
 */

namespace App;


use App\Exceptions\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DiffBuckS3Helper {

    use UploadHelper;
    use LocalDirectoryHelper;

    protected $file_name;
    protected $results = [];


    /**
     * @TODO refactor this to only care about source and destinations
     * so it is reusable
     *
     * @param PDF2FilesHandler $filesHandler
     * @return mixed
     */
    public function getFile(PDF2FilesHandler $filesHandler)
    {

        //Set up the conventions for location
        $this->setRequestUuid($filesHandler->getRequestId());
        $this->setProjectId($filesHandler->getProjectId());
        $this->figureOutRootPath();
        $this->setLocalDestinationRoot($this->getDiffsRequestFolder());

        //See if file exists
        $this->file_name = $filesHandler->getSet() . '.pdf';
        $file = Storage::disk('s3')->get($this->getBundleRequestOriginalsFolder() . $this->file_name);


        File::put($this->getLocalUploadDir() . $this->file_name, $file);
        return $file;
    }

    public function putFilesToS3($source, $destination)
    {
        $this->setupFolder($source, $destination);

        $directories = File::directories($source);

        $this->iterateOverDirectories($destination, $directories);

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

    private function setupFolder($source, $destination)
    {
        if(!Storage::disk('s3')->exists($destination))
        {
            Storage::disk('s3')->makeDirectory($destination);
        }
    }

    /**
     * @param $destination
     * @param $files
     * @param $name
     * @return array
     */
    protected function copyFileToS3($destination, $files, $name)
    {
        foreach ($files as $file) {
            $content = File::get($file);
            $exploded = explode("/", $file);
            $file_name = array_pop($exploded);
            $this->setResults(sprintf("Adding file to s3 %s", $file_name));
            Storage::disk('s3')->put($destination . '/' . $name . '/' . $file_name, $content);
        }
    }

    private function iterateOverDirectories($destination, $directories)
    {
        foreach($directories as $dir)
        {
            $exploded = explode("/", $dir);
            $name = array_pop($exploded);

            if(!Storage::disk('s3')->exists($destination . '/' . $name))
            {
                Storage::disk('s3')->makeDirectory($destination . '/' . $name);
            }

            $files = File::files($dir);

            $this->copyFileToS3($destination, $files, $name);
        }
    }

    /**
     * @return mixed
     */
    public function getResults()
    {
        return implode("\n", $this->results);
    }

    /**
     * @param mixed $results
     */
    public function setResults($results)
    {
        $this->results[] = $results;
    }


}