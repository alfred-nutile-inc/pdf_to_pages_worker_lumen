<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/8/15
 * Time: 7:26 PM
 */

namespace App;


class CompareDTO {

    public $path;
    public $custom_name;
    public $dirname;
    public $timestamp;
    public $size;
    public $type;
    public $basename;
    public $extension;
    public $filename;
    public $url;
    public $quick_diff;
    public $original_page;

    public function __construct(
        $custom_name = false,
        $basename,
        $url = false,
        $quick_diff = false,
        $original_page = false
    )
    {

        $this->custom_name = $custom_name;
        $this->basename = $basename;
        $this->url = $url;
        $this->quick_diff = $quick_diff;
        $this->original_page = $original_page;
    }
}
