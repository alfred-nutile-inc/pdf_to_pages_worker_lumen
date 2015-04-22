<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/21/15
 * Time: 10:02 AM
 */

namespace App;


use AlfredNutileInc\DiffTool\DiffToolDTO;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PDF2FilesHandler {

    use LocalDirectoryHelper;
    use PathHelper;

    protected $results = [];
    protected $set;

    protected $run_pdftk_output_destination;
    protected $pdftk_source;

    protected $convert_source;
    protected $convert_destination;
    protected $source_upload_done_files;
    protected $destination_upload_done_files;

    /**
     * @var PDFTKHelper
     */
    private $PDFTKHelper;
    /**
     * @var DiffBuckS3Helper
     */
    private $diffBuckS3Helper;

    public $request_id;
    public $project_id;

    /**
     * @var ConvertToImages
     */
    private $convertToImages;

    public function __construct(
        PDFTKHelper $PDFTKHelper = null,
        DiffBuckS3Helper $diffBuckS3Helper = null,
        ConvertToImages $convertToImages = null)
    {
        $this->PDFTKHelper = ($PDFTKHelper == null) ? new PDFTKHelper() : $PDFTKHelper;
        $this->diffBuckS3Helper = ($diffBuckS3Helper == null) ? new DiffBuckS3Helper() : $diffBuckS3Helper;
        $this->convertToImages = ($convertToImages == null) ? new ConvertToImages() : $convertToImages;
    }

    public function handle(DiffToolDTO $payload)
    {
        $this->setRequestId($payload->request_id);
        $this->setSet($payload->set);
        $this->setProjectId($payload->project_id);
        $this->setLocalDestinationRoot($this->getDiffsRequestFolder());

        //See if this is on S3

        $this->diffBuckS3Helper->getFile($this);

        $output = $this->breakIntoPages();
        $this->setResults($output);

        $output = $this->breakIntoImages();
        $this->setResults($output);

        $output = $this->moveFilesBackToS3();
        $this->setResults($output);

        $this->setResults("Done Working on PDF to Images");
    }

    protected function moveFilesBackToS3()
    {
        $this->source_upload_done_files         = $this->getLocalDestinationRoot() . '/diffs';
        $this->destination_upload_done_files    = $this->getDiffsRequestFolder() . '/diffs';

        //Make sure the folder Exists
        $this->diffBuckS3Helper->putFilesToS3($this->source_upload_done_files, $this->destination_upload_done_files);

        //Then Copy all the files up to there
        return $this->diffBuckS3Helper->getResults();

    }

    protected function breakIntoPages()
    {
        $this->setPdftkSourceFile($this->getLocalUploadDir() . $this->getSet() . '.pdf');
        $this->setRunPdftkOutputDestination($this->getLocalPDFsToPages($this->getSet()));

        if(!File::exists($this->getRunPdftkOutputDestination()))
            File::makeDirectory($this->getRunPdftkOutputDestination(), 0755, $recursive = true, $force = true);

        $this->PDFTKHelper->run($this->getPdftkSource(), $this->getRunPdftkOutputDestination());
        return implode("\n", $this->PDFTKHelper->getPdftkHelperOutput());
    }

    protected function breakIntoImages()
    {
        $this->convert_source = $this->getBundleRequestRootFolderLocal(
                $this->getProjectId(), $this->getRequestId()) . '/diffs/pdf' . $this->getSet() . '_to_pages';

        $this->convert_destination = $this->getBundleRequestRootFolderLocal(
                $this->getProjectId(), $this->getRequestId()) . '/diffs/pdf' . $this->getSet() . '_to_images';

        if(!File::exists($this->convert_destination))
            File::makeDirectory($this->convert_destination, 0755, $recursive = true);

        $this->convertToImages->convert($this->convert_source, $this->convert_destination, $this->getSet());
        return implode("\n", $this->convertToImages->getResults());
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
     * @return mixed
     */
    public function getRunPdftkOutputDestination()
    {
        return $this->run_pdftk_output_destination;
    }

    /**
     * @param mixed $run_pdftk_output_destination
     */
    public function setRunPdftkOutputDestination($run_pdftk_output_destination)
    {
        $this->run_pdftk_output_destination = $run_pdftk_output_destination;
    }

    /**
     * @return mixed
     */
    public function getPdftkSource()
    {
        return $this->pdftk_source;
    }

    /**
     * @param mixed $pdftk_source_file
     */
    public function setPdftkSourceFile($pdftk_source_file)
    {
        $this->pdftk_source = $pdftk_source_file;
    }

    /**
     * @return mixed
     */
    public function getConvertSource()
    {
        return $this->convert_source;
    }

    /**
     * @param mixed $convert_source
     */
    public function setConvertSource($convert_source)
    {
        $this->convert_source = $convert_source;
    }

    /**
     * @return mixed
     */
    public function getConvertDestination()
    {
        return $this->convert_destination;
    }

    /**
     * @param mixed $convert_destination
     */
    public function setConvertDestination($convert_destination)
    {
        $this->convert_destination = $convert_destination;
    }

    /**
     * @return mixed
     */
    public function getSourceUploadDoneFiles()
    {
        return $this->source_upload_done_files;
    }

    /**
     * @param mixed $source_upload_done_files
     */
    public function setSourceUploadDoneFiles($source_upload_done_files)
    {
        $this->source_upload_done_files = $source_upload_done_files;
    }

    /**
     * @return mixed
     */
    public function getDestinationUploadDoneFiles()
    {
        return $this->destination_upload_done_files;
    }

    /**
     * @param mixed $destination_upload_done_files
     */
    public function setDestinationUploadDoneFiles($destination_upload_done_files)
    {
        $this->destination_upload_done_files = $destination_upload_done_files;
    }


}