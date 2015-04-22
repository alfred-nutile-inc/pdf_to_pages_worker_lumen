<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/21/15
 * Time: 10:22 AM
 */

namespace App;


class PDFTKHelper {

    protected $pdftk_helper_output = [];

    public function __construct()
    {

    }

    public function run($file, $destination)
    {

        $pdftk = base_path("bin/pdftk");

        $base_path = base_path();

        $export1 = 'export PATH=' . $base_path . 'bin:$PATH';
        $export2 = 'export LD_LIBRARY_PATH=' . $base_path . '/lib:' . $base_path . '/lib/x86_64-linux-gnu:/usr/local/lib:$LD_LIBRARY_PATH';
        $command = "$export1 && $export2 && chmod +x {$pdftk}.target && {$pdftk}.target {$file} burst output {$destination}/page_%03d.pdf";

        exec("rm -f {$destination}/*.pdf", $output, $results);
        $this->pdftk_helper_output = $this->pdftk_helper_output + $output;

        exec($command, $output, $results);
        $this->pdftk_helper_output = $this->pdftk_helper_output + $output;

    }

    /**
     * @return array
     */
    public function getPdftkHelperOutput()
    {
        return $this->pdftk_helper_output;
    }

    /**
     * @param array $pdftk_helper_output
     */
    public function setPdftkHelperOutput($pdftk_helper_output)
    {
        $this->pdftk_helper_output = $pdftk_helper_output;
    }

}