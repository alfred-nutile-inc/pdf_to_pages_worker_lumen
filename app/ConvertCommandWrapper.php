<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/26/15
 * Time: 8:35 AM
 */

namespace App;


class ConvertCommandWrapper {

    /**
     * Base path where compares folder is
     * under a and b folder
     * eg /var/root/bundles/mock-project/requests/mock-request/compares
     * @var
     */
    public $root;
    public $destination;

    public function createDiffCommand($file1, $file2, $key)
    {
        $this->destination = $this->getRoot() . '/diff_images/compared-a-b-page_' . $key . '.png';
        $name = 'output_' . $key;
        $command = "compare -metric PSNR {$file1} {$file2} {$this->destination} 2> /tmp/{$name}.txt";
        return $command;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        if($this->root == null)
            throw new \Exception("Root needs to be set for convert");
        return $this->root;
    }

    /**
     * @param mixed $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
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


}