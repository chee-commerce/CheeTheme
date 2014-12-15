<?php namespace Chee\Theme\Models;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ImageSize extends \Eloquent
{
    use SoftDeletingTrait;

    protected $primaryKey = 'image_size_id';

    protected $table =  'image_sizes';

    public $timestamps = false;

    protected $dates = ['deleted_at'];

    protected $fillable = array('image_size_name', 'image_size_width', 'image_size_height', 'image_size_quality', 'image_size_usage', 'deleted_at');
}
