<?php namespace Chee\Theme\Models;

class ImageSize extends \Eloquent
{
    protected $primaryKey = 'image_size_id';

    protected $table =  'image_sizes';
    
    public $timestamps = false;
}
