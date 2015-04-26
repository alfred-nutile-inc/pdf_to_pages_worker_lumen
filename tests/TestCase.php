<?php

use Illuminate\Support\Facades\File;

class TestCase extends Laravel\Lumen\Testing\TestCase {

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public function setFilesForDiff()
    {
        $dest = storage_path('bundles/mock-project-1');
        if(File::exists($dest))
            File::deleteDirectory($dest);

        File::copyDirectory(base_path('tests/fixtures/bundles'), storage_path('bundles'));

        foreach(range(0,5) as $index)
        {
            file_put_contents('/tmp/output_' . $index . '.txt', $index + 10);
        }

    }

}
