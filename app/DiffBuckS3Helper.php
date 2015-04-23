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

        //See if folder is set and writable

        File::put($this->getLocalUploadDir() . $this->file_name, $file);
        return $file;
    }

    public function getAllFilesForDiff($destination, $source)
    {
        foreach(['a', 'b'] as $set)
        {
            if(!File::exists($destination . '/' . $set))
                File::makeDirectory($destination . '/' . $set, 0755, $recursive = true);

            $path  = $source . '/' . $set;
            $files = Storage::disk('s3')->files($path);

            foreach($files as $file)
            {
                $content = Storage::disk('s3')->get($file);
                $file_name = $this->getFileNameFromPath($file);
                File::put($destination . '/' . $set . '/' . $file_name, $content);
            }
        }

        $content = Storage::disk('s3')->get($source . '/compare.json');
        File::put($destination . '/compare.json', $content);
    }

    public function putFilesToS3($source, $destination, $set)
    {
        $this->setupFolder($source, $destination, $set);

        if($set == 'compare.json')
        {
            $content = File::get($source);

            Storage::disk('s3')->put($destination . '/compare.json', $content);
        }
        else
        {
            $directories = File::directories($source);

            $this->iterateOverDirectories($destination, $directories, $set);
        }


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

    private function setupFolder($source, $destination, $set = 'a')
    {

        if (!Storage::disk('s3')->exists($destination)) {
            Storage::disk('s3')->makeDirectory($destination);
        }

        if($set != 'compare.json') {

            if (!Storage::disk('s3')->exists($destination . '/' . $set)) {
                Storage::disk('s3')->makeDirectory($destination . '/' . $set);
            }

            if (!Storage::disk('s3')->exists($destination . '/original_pages/' . $set)) {
                Storage::disk('s3')->makeDirectory($destination . '/original_pages/' . $set);
            }
        }

    }

    /**
     * @param $destination
     * @param $files
     * @param $name
     * @return array
     */
    protected function copyFileToS3($destination, $files, $set, $type = 'images')
    {
        foreach ($files as $file) {
            $content = File::get($file);
            $file_name = $this->getFileNameFromPath($file);

            $this->setResults(sprintf("Adding file to s3 %s", $file_name));
            if($type == 'images')
            {
                Storage::disk('s3')->put($destination . '/' . $set . '/' . $file_name, $content);
            } else {

                Storage::disk('s3')->put($destination . '/original_pages/' . $set . '/' . $file_name, $content);
            }
        }
    }

    private function iterateOverDirectories($destination, $directories, $set)
    {
        foreach($directories as $dir)
        {
            /**
             * We do not need to upload pages just images
             * but for counting reason we upload pagees too
             */
            if(strpos($dir, '_images') > 0)
            {
                $files = File::files($dir);
                $this->copyFileToS3($destination, $files, $set, 'images');
            } else
            {
                $files = File::files($dir);
                $this->copyFileToS3($destination, $files, $set, 'pages');
            }
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

    private function getFileNameFromPath($file)
    {
        $exploded = explode("/", $file);
        $file_name = array_pop($exploded);
        return $file_name;
    }


}