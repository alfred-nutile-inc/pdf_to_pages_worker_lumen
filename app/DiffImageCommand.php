<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/23/15
 * Time: 1:19 PM
 */

namespace App;


use App\Helpers\CompareJsonHelper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class DiffImageCommand {


    use CompareJsonHelper;
    
    protected $smaller_collection;
    protected $file_one_array;
    protected $file_two_array;
    protected $file_one_count;
    protected $file_two_count;
    protected $destination;
    protected $root;
    protected $both_files_done_diffing;


    public function createDiffOfImages($root)
    {
        $jobs = [];
        $this->file_one_array = File::files($root . '/a');
        $this->file_two_array = File::files($root . '/b');
        $this->root = $root;

        $this->setTheSmallerCollection();
        $this->setFileOneCount(1);
        $this->setFileTwoCount(count($this->file_two_array));

        $this->step3 = 'RUNNING';
        $message = "Step 3: Compare PDF 1 pages to PDF 2 pages is RUNNING";

        $this->triggerEvent($message, 'quick_diff_start', $total_files = count($this->file_one_array));
        Log::info($message);

        foreach($this->file_one_array as $key => $file1)
        {
            if($this->doneProcessingDiffOfPdfs()) //In case one folder has more than the other
                break;
            ////
            $file2 = $this->file_two_array[$key];
            if(strpos($file1, '.txt') === false)
            {
                Log::info("Make diff");
                Log::info($file1);
                Log::info($file2);
                $command = $this->createDiffCommand($file1, $file2, $key);

                $process = (new Process($command));
                $process->setTimeout(700);
                $process->setIdleTimeout(120);
                $process->enableOutput();

                $process->start();

                $key = $this->file_one_count; //this should match the item in the array;

                $jobs[$key]['process'] = $process;
                $jobs[$key]['data']    = ['set' => 'images_a', 'key' => $key, 'count' => $this->file_one_count];
                $this->file_one_count++;
            }
        }

        while(count($jobs) > 0)
        {
            foreach($jobs as $key => $job)
            {
                if(!$job['process']->isRunning())
                {
                    $results = $this->getCompareDiffResultsFromCommandOutput($key);
                    $this->triggerEvent($message, 'quick_diff_progress', $total_files = $job['data']['count']);
                    $this->updateCompareJson($job, $results);
                    unset($jobs[$key]);
                }
            }
        }

        $message = "Step 3: Compare PDF 1 pages to PDF 2 pages is DONE";

        $this->triggerEvent($message, 'quick_diff_done', $total_files = count($this->file_two_array));
        $this->setBothFilesDoneDiffing(true);
        Log::info($message);

        return $this->compare_json_state;
    }

    protected function triggerEvent()
    {

    }

    protected function updateCompareJson($job, $results)
    {
        $set            = $job['data']['set']; //right now there is only a to consider
        $name           = 'quick_diff';
        $value          = $results;
        $compare_key    = $job['data']['key'];
        $this->updateCompareValue($set, $compare_key, $name, $value);
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param mixed $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    protected function getCompareDiffResultsFromCommandOutput($key)
    {
        $results = file_get_contents("/tmp/output_{$key}.txt");
        $results = (trim($results) == 'inf') ? 0 : trim($results); //inf = no change I want 0
        $results = (strpos($results, 'compare.im6: image widths or heights differ') !== false) ? 1000 : $results;
        return $results;
    }

    protected function createDiffCommand($file1, $file2, $key)
    {
        $this->destination = $this->getRoot() . '/compares/diff_images/compared-a-b-page_' . $key . '.png';
        $name = 'output_' . $key;
        $command = "compare -metric PSNR {$file1} {$file2} {$this->destination} 2> /tmp/{$name}.txt";
        return $command;
    }

    protected function setTheSmallerCollection()
    {
        if(count($this->file_one_array) <= count($this->file_two_array))
        {
            $this->smaller_collection = 'a';
        } else {
            $this->smaller_collection = 'b';
        }
    }

    /**
     * @return mixed
     */
    public function getFileOneCount()
    {
        return $this->file_one_count;
    }

    /**
     * @param mixed $file_one_count
     */
    public function setFileOneCount($file_one_count)
    {
        $this->file_one_count = $file_one_count;
    }

    /**
     * @return mixed
     */
    public function getFileTwoCount()
    {
        return $this->file_two_count;
    }

    /**
     * @param mixed $file_two_count
     */
    public function setFileTwoCount($file_two_count)
    {
        $this->file_two_count = $file_two_count;
    }

    protected function doneProcessingDiffOfPdfs()
    {

        if($this->smaller_collection == 'a')
        {
            if($this->file_one_count > count($this->file_one_array))
                return true;
        }

        if($this->smaller_collection == 'b')
        {
            if($this->file_one_count > count($this->file_two_array))
                return true;
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function isBothFilesDoneDiffing()
    {
        return $this->both_files_done_diffing;
    }

    /**
     * @param boolean $both_files_done_diffing
     */
    public function setBothFilesDoneDiffing($both_files_done_diffing)
    {
        $this->both_files_done_diffing = $both_files_done_diffing;
    }
}