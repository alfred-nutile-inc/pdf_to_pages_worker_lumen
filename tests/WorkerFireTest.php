<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mockery as m;

class WorkerFireTest extends TestCase {


    protected $mock_convert;
    protected $mock_pdftk;

    public function setUp()
    {
        parent::setUp();

        $this->mock_convert   = m::mock('App\ConvertToImages');
        $this->mock_pdftk = m::mock('App\PDFTKHelper');

    }

    /**
     * @test
     */
    public function test_that_s3_helper_is_called()
    {
        $payload    = $this->getPayload();

        /**
         * Assert this is called
         */
        $this->mock_pdftk->shouldReceive('run');
        $this->mock_pdftk->shouldReceive('getPdftkHelperOutput')->andReturn(["You are mocked"]);

        $this->mock_convert->shouldReceive('convert')->andReturn(true);
        $this->mock_convert->shouldReceive('getResults')->andReturn([]);


        $worker     = new  \App\PDF2FilesHandler($this->mock_pdftk, null, $this->mock_convert);
        $this->setS3Storage();
        $this->setFileFacade();

        $worker->handle($payload);
    }

    /**
     * @test
     */
    public function test_that_paths_are_set_for_pdftk()
    {
        $payload    = $this->getPayload();

        $this->mock_pdftk->shouldReceive('run');
        $this->mock_pdftk->shouldReceive('getPdftkHelperOutput')->andReturn(["You are mocked"]);
        $this->mock_convert->shouldReceive('convert')->andReturn(true);
        $this->mock_convert->shouldReceive('getResults')->andReturn([]);

        $this->setS3Storage();

        $this->setFileFacade();

        $worker     = new  \App\PDF2FilesHandler($this->mock_pdftk, null, $this->mock_convert);


        $worker->handle($payload);

        $this->assertContains('storage/bundles/mock-project-1/requests/mock-request-5/diffs/pdfa_to_pages', $worker->getRunPdftkOutputDestination());
        $this->assertContains('storage/bundles/mock-project-1/requests/mock-request-5/uploads/a.pdf', $worker->getPdftkSource());
    }

    /**
     * @test
     */
    public function should_break_info_images()
    {
        //clean up folder
        //run from the top
        if(File::exists(storage_path('bundles/mock-project-1')))
        {
            File::deleteDirectory(storage_path('bundles/mock-project-1'));
        }

        $this->setS3Storage();
        $this->setFileFacade();

        $payload        = $this->getPayload();

        $this->mock_pdftk->shouldReceive('run');
        $this->mock_pdftk->shouldReceive('getPdftkHelperOutput')->andReturn(["You are mocked"]);

        /**
         * Assert this is called
         */
        $this->mock_convert->shouldReceive('convert')->once()->andReturn(true);
        $this->mock_convert->shouldReceive('getResults')->once()->andReturn([]);

        $worker     = new  \App\PDF2FilesHandler($this->mock_pdftk, null, $this->mock_convert);


        $worker->handle($payload);

        //Assert paths are right
        $this->assertContains('storage/bundles/mock-project-1/requests/mock-request-5/diffs/pdfa_to_images', $worker->getConvertDestination());
        $this->assertContains('storage/bundles/mock-project-1/requests/mock-request-5/diffs/pdfa_to_images', $worker->getConvertDestination());

    }

    /**
     * @test
     */
    public function catch_errors_on_file_not_found()
    {
        //Catch the errors and notify requester via the queue
        //or a callback
    }

    /**
     * @test
     */
    public function should_upload_files_to_s3()
    {

        $this->addFilesToSource();

        $this->setS3Storage();
        $this->setFileFacade();

        $payload        = $this->getPayload();

        $this->mock_pdftk->shouldReceive('run');
        $this->mock_pdftk->shouldReceive('getPdftkHelperOutput')->andReturn(["You are mocked"]);

        $this->mock_convert->shouldReceive('convert')->once()->andReturn(true);
        $this->mock_convert->shouldReceive('getResults')->once()->andReturn([]);

        $mock_s3 = m::mock('\App\DiffBuckS3Helper');
        $mock_s3->shouldReceive('getFile')->andReturn('foo');
        $mock_s3->shouldReceive('getResults')->andReturn(['foo']);
        /**
         * Asserting this is called
         */
        $mock_s3->shouldReceive('putFilesToS3')->once()->andReturn(true);

        $worker     = new  \App\PDF2FilesHandler($this->mock_pdftk, $mock_s3, $this->mock_convert);

        $worker->handle($payload);

        //Assert paths are right
        $this->assertContains('storage/bundles/mock-project-1/requests/mock-request-5/diffs/pdfa_to_images', $worker->getConvertDestination());
        $this->assertContains('storage/bundles/mock-project-1/requests/mock-request-5/diffs/pdfa_to_images', $worker->getConvertDestination());
    }

    /**
     * @test
     */
    public function real_run_if_needed()
    {
        $this->markTestSkipped("Not needed at test time but if you want to see it run all the way");
        $this->addFilesToSource();

        $payload        = $this->getPayload();

        $worker     = new  \App\PDF2FilesHandler();

        $worker->handle($payload);
    }

    /**
     * @test
     */
    public function should_call_queue_to_say_done()
    {
        //Done with file or done with ALL images or?
        //Pusher or just the queue?
    }

    protected function addFilesToSource()
    {
        $source = base_path('tests/fixtures/bundles');
        $destination = storage_path('bundles');

        File::copyDirectory($source, $destination);
    }

    protected function setFileFacade()
    {
        File::shouldReceive('makeDirectory');
        File::shouldReceive('directories')->andReturn(['foo', 'bar']);
        File::shouldReceive('deleteDirectory')->andReturn(true);
        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')->andReturn('foo bar');
        File::shouldReceive('put')->andReturn(true);
        File::shouldReceive('copyDirectory')->andReturn(['foo', 'bar']);
        File::shouldReceive('files')->andReturn(['foo', 'bar']);
    }

    protected function getPayload($request_uuid = "mock-request-5", $project_id = 'mock-project-1', $set = 'a')
    {
        $dto = new \AlfredNutileInc\DiffTool\DiffToolDTO($project_id, $request_uuid, false, false, $set);

        return $dto;
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    private function setS3Storage()
    {
        $content    = file_get_contents(base_path('demo/from/pdf1.pdf'));
        //This helped me get around an issue with disk
        $get        = m::mock();
        $get->shouldReceive('createDriver')->andReturnSelf();
        $get->shouldReceive('get')->andReturn($content);
        $get->shouldReceive('put')->andReturn(true);
        $get->shouldReceive('exists')->andReturn(true);
        $get->shouldReceive('makeDirectory')->andReturn(true);

        Storage::shouldReceive('disk')->andReturn($get);
    }
}
