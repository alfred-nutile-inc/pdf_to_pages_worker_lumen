<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/23/15
 * Time: 12:49 PM
 */

namespace App;


use AlfredNutileInc\DiffTool\DiffToolDTO;
use App\Helpers\CompareJsonHelper;
use Illuminate\Support\Facades\File;
use Mockery\CountValidator\Exception;

class DiffImagesHandler extends BaseHandler {


    /**
     * @var DiffBuckS3Helper
     */
    public $diffBuckS3Helper;
    public $compares_source;
    public $compares_destination;
    /**
     * @var DiffImageCommand
     */
    private $diffImageCommand;

    public function __construct(
        DiffBuckS3Helper $diffBuckS3Helper = null,
        DiffImageCommand $diffImageCommand = null)
    {
        $this->diffBuckS3Helper = ($diffBuckS3Helper == null) ? new DiffBuckS3Helper() : $diffBuckS3Helper;
        $this->diffImageCommand = ($diffImageCommand == null) ? new DiffImageCommand() : $diffImageCommand;
    }

    public function handle(DiffToolDTO $payload)
    {
        $this->setDto($payload);
        $this->setRequestId($payload->request_id);
        $this->setSet($payload->set);
        $this->setProjectId($payload->project_id);
        $this->setLocalDestinationRoot($this->getDiffsRequestFolder());
        $this->compares_source        = $this->getDiffsRequestFolder() . '/compares';
        $this->compares_destination   = $this->getLocalDestinationRoot() . '/compares';

        //Get the files
        try
        {
            $this->diffBuckS3Helper->getAllFilesForDiff(
                $this->compares_destination, $this->compares_source);
        }
        catch(\Exception $e)
        {
            throw new \Exception(sprintf("Error getting files for diff request id %s message %s",
                $this->getRequestId(), $e->getMessage()));
        }

        try
        {
            $this->loadCompareState();
        }
        catch(\Exception $e)
        {
            throw new Exception(sprintf("Compares not found for project %s and request %s in %s \n messageL %s",
                $this->getProjectId(), $this->getRequestId(), $this->getDiffsRequestFolder(), $e->getMessage()));
        }

        try
        {
            /**
             * Ugly
             */
            $this->compare_json_state = $this->diffImageCommand->createDiffOfImages($this->compares_destination, $this->compare_json_state);
        }
        catch(\Exception $e)
        {
            throw new \Exception(sprintf("Error making diffs request id %s message %s",
                $this->getRequestId(), $e->getMessage()));
        }

        try
        {
            $output = $this->writeCompareAndDiffsBackToS3();
            $this->setResults($output);
        }
        catch(\Exception $e)
        {
            throw new \Exception(sprintf("Error sending files back to S3 %s", $e->getMessage()));
        }

        return "Done getting images";

    }

    public function loadCompareState()
    {

        $this->loadCompareFromFile($this->compares_destination . '/compare.json');
    }

    private function writeCompareAndDiffsBackToS3()
    {
        $this->diffBuckS3Helper->putDiffImagesOnS3($this->compares_destination . '/diff_images', $this->compares_source . '/diff_images');
        $content = json_encode($this->getCompareJsonState(), JSON_PRETTY_PRINT);
        $this->diffBuckS3Helper->putCompareOnS3($content, $this->compares_source . '/compare.json');
        return "Wrote compare to s3";
    }

}