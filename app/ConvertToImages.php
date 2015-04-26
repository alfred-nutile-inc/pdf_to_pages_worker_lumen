<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/21/15
 * Time: 1:07 PM
 */

namespace App;


use App\Helpers\CompareJsonHelper;
use App\Helpers\PusherTrait;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ConvertToImages {

    use CompareJsonHelper;
    use PathHelper;

    protected $project_id;
    protected $request_id;


    protected $imageDestination;
    protected $density;

    protected $source;
    protected $destination;

    protected $set;

    protected $results = [];

    public function convert($source, $destination, $set)
    {
        $jobs = [];



        $this->setSource($source);
        $this->setDestination($destination);
        $this->setSet($set);


        $files = File::files($this->getSource());
        $count = 1;


        foreach($files as $file)
        {
            Log::info($file);
            if(strpos($file, '.txt') === false)
            {
                $command = $this->buildPDF2ImageCommand($this->getSet(), $file, $count);
                $process = (new Process($command['command']));
                $process->setTimeout(700);
                $process->setIdleTimeout(120);
                $process->start();
                $jobs[$set . ':' . $count]['process'] = $process;
                $jobs[$set . ':' . $count]['data']    = $command['data'];
                $jobs[$set . ':' . $count]['path']    = $file;
                $count++;
            }
        }


        while(count($jobs) > 0)
        {
            foreach($jobs as $key => $job)
            {
                if(!$job['process']->isRunning())
                {
                    if($job['process']->getExitCode() !== 0)
                    {
                        $message = sprintf("Error running pdf to images job %s",
                            $job['process']->getErrorOutput());
                        Log::info($message);
                        $this->setResults("Error $message");
                        //@NOTE not sure if I should throw here.
                        //throw new \Exception($message);
                    }
                    $compare_key = $job['data']['count']; //set page 1 and up so we have the order even if they render out of order
                    $dto = $this->buildDto( $job['data']['compare_dto'], $compare_key);
                    $this->addCompareNode($dto, 'images_' . $job['data']['set'], $compare_key);
                    $this->setResults($job['path']);
                    unset($jobs[$key]);
                }
            }
        }



        return $this->compare_json_state;

    }

    protected function buildPDF2ImageCommand($set, $full_path_to_pdf, $count)
    {

        $count_formatted = $this->returnNumberFormatted($count);
        $this->imageDestination =  $this->getDestination();
        $image_name = "page_{$count_formatted}.png";

        $storage = storage_path();
        $temp = "export MAGICK_TMPDIR={$storage}";

        $command = "$temp && convert -density {$this->getDensity()} {$full_path_to_pdf} {$this->imageDestination}/$image_name";

        $compare_data = ['image_destination' => $this->getDestination(),
            'image_name' => $image_name];

        return ['command' => $command, 'data' => ['compare_dto' => $compare_data, 'set' => $set, 'count' => $count]];
    }

    protected function returnNumberFormatted($number)
    {
        return sprintf("%03d", $number);
    }

    public function getDensity()
    {
        if($this->density == null)
            $this->setDensity();
        return $this->density;
    }

    public function setDensity($density = 600)
    {
        $this->density = $density;
    }




    /**
     * @return mixed
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param mixed $results
     */
    public function setResults($results)
    {
        $this->results[] = $results;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param mixed $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
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


}