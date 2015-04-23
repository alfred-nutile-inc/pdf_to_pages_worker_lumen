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

class DiffImagesHandler extends BaseHandler {

    use LocalDirectoryHelper;
    use CompareJsonHelper;
    use PathHelper;

    /**
     * @var DiffBuckS3Helper
     */
    public $diffBuckS3Helper;
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
        $compares_source        = $this->getDiffsRequestFolder() . '/compares';
        $compares_destination   = $this->getLocalDestinationRoot() . '/compares';

        //Get the files
        try
        {
            $this->diffBuckS3Helper->getAllFilesForDiff(
                $compares_destination, $compares_source);
        }
        catch(\Exception $e)
        {
            throw new \Exception(sprintf("Error getting files for diff request id %s message %s",
                $this->getRequestId(), $e->getMessage()));
        }

        //Start the diff process on the files
        try
        {
            $this->compare_json_state = $this->diffImageCommand->createDiffOfImages($compares_destination);
        }
        catch(\Exception $e)
        {
            throw new \Exception(sprintf("Error making diffs request id %s message %s",
                $this->getRequestId(), $e->getMessage()));
        }

        return "Done getting images";

    }

}