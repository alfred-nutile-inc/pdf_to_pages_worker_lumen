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
        $path,
        $dirname,
        $custom_name = false,
        $timestamp = false,
        $size = false,
        $type = 'file',
        $basename,
        $extension = 'jpg',
        $filename,
        $url = false,
        $quick_diff = false,
        $original_page = false
    )
    {

        $this->path = $path;
        $this->custom_name = $custom_name;
        $this->dirname = $dirname;
        $this->timestamp = $timestamp;
        $this->size = $size;
        $this->type = $type;
        $this->basename = $basename;
        $this->extension = $extension;
        $this->filename = $filename;
        $this->url = $url;
        $this->quick_diff = $quick_diff;
        $this->original_page = $original_page;
    }
}
