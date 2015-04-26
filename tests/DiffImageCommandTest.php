<?php
use Illuminate\Support\Facades\File;

/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/26/15
 * Time: 8:20 AM
 */

use Mockery as m;
use App\ConvertCommandWrapper;

class DiffImageCommandTest extends \TestCase {

    use \App\Helpers\CompareJsonHelper;

    /**
     * @test
     */
    public function make_fixture()
    {
        $this->markTestSkipped();

        $this->setFilesForDiff();
        $path = storage_path('bundles/mock-project-1/requests/mock-request-1');
        $mockDiffTool = m::mock('App\ConvertCommandWrapper');
        $mockDiffTool->shouldReceive('createDiffCommand')->andReturn(true);
        $mockDiffTool->shouldReceive('setRoot')->andReturn(true);
        $this->loadCompareFromFile($path . '/compares/compare.json');
        $diff = new \App\DiffImageCommand($mockDiffTool, $this->compare_json_state);
        $content = json_encode($diff->getCompareJsonState(), JSON_PRETTY_PRINT);
        file_put_contents(base_path('tests/fixtures/composer_with_diffs.json'), $content);
    }

    /**
     * @test
     */
    public function should_set_a_and_b_count()
    {
        $this->setFilesForDiff();
        $path = storage_path('bundles/mock-project-1/requests/mock-request-1');
        $mockDiffTool = m::mock('App\ConvertCommandWrapper');
        $mockDiffTool->shouldReceive('createDiffCommand')->andReturn(true);
        $mockDiffTool->shouldReceive('setRoot')->andReturn(true);
        $this->loadCompareFromFile($path . '/compares/compare.json');
        $diff = new \App\DiffImageCommand($mockDiffTool, $this->compare_json_state);

        $diff->createDiffOfImages($path . '/compares', $this->compare_json_state);
        $this->assertEquals(6, count($diff->getFileOneArray()));
        $this->assertEquals(6, count($diff->getFileTwoArray()));


    }

    /**
     * @test
     */
    public function should_set_a_as_smaller_count()
    {
        $this->setFilesForDiff();
        $path = storage_path('bundles/mock-project-1/requests/mock-request-1');

        $mockDiffTool = m::mock('App\ConvertCommandWrapper');
        $mockDiffTool->shouldReceive('createDiffCommand')->andReturn(true);
        $mockDiffTool->shouldReceive('setRoot')->andReturn(true);
        $this->loadCompareFromFile($path . '/compares/compare.json');
        $diff = new \App\DiffImageCommand($mockDiffTool, $this->compare_json_state);

        $diff->createDiffOfImages($path . '/compares', $this->compare_json_state);
        $this->assertEquals('a', $diff->getSmallerCollection());
    }

    /**
     * @test
     */
    public function should_update_compare_and_not_overwrite()
    {
        $this->setFilesForDiff();
        $path = storage_path('bundles/mock-project-1/requests/mock-request-1');
        $mockDiffTool = m::mock('App\ConvertCommandWrapper');
        $mockDiffTool->shouldReceive('createDiffCommand')->andReturn(true);
        $mockDiffTool->shouldReceive('setRoot')->andReturn(true);
        $this->loadCompareFromFile($path . '/compares/compare.json');
        $diff = new \App\DiffImageCommand($mockDiffTool, $this->compare_json_state);

        $results = $diff->createDiffOfImages($path . '/compares', $this->compare_json_state);

        $this->assertEquals('10', $results['images_a'][0]['quick_diff']);
        $this->assertEquals('15', $results['images_a'][5]['quick_diff']);
    }




    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

}