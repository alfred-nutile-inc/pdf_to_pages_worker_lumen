<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/26/15
 * Time: 10:00 AM
 */

use AlfredNutileInc\DiffTool\DiffToolDTO;
use App\DiffBuckS3Helper;
use Mockery as m;
class DiffImagesHandlerTest extends \TestCase {

    use \App\Helpers\CompareJsonHelper;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function should_load_compare_json()
    {
        $this->setFilesForDiff();
        $path                   = storage_path('bundles/mock-project-1/requests/mock-request-1/compares');
        $s3                     = m::mock('App\DiffBuckS3Helper');
        $s3->shouldReceive('getAllFilesForDiff')->andReturn(true);
        $s3->shouldReceive('putDiffImagesOnS3')->andReturn(true);
        $s3->shouldReceive('putCompareOnS3')->andReturn(true);
        $diffImageCommand       = m::mock('App\DiffImageCommand');
        $this->loadCompareFromFile(base_path('tests/fixtures/composer_with_diffs.json'));
        $diffImageCommand->shouldReceive('createDiffOfImages')->andReturn($this->compare_json_state);
        $this->loadCompareFromFile($path . '/compare.json');
        $diffHandler = new \App\DiffImagesHandler($s3, $diffImageCommand);
        $dto = $this->dto();
        $diffHandler->handle($dto);

        $this->assertEquals('mock-project-1', $diffHandler->compare_json_state['project_id']);
        $this->assertEquals(10, $diffHandler->compare_json_state['images_a'][0]['quick_diff']);
    }

    public function should_write_compare_state_to_s3_with_latest_diff_results()
    {

    }

    public function should_upload_diff_images()
    {

    }

    public function dto()
    {
        return new DiffToolDTO('mock-project-1', 'mock-request-1', $state = 0, false, $set = '1', 0);
    }


    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}