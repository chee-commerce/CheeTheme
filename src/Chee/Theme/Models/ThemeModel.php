<?php namespace Chee\Theme\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class ThemeModel extends Model {

    public $table = 'themes';

    public $timestamps = false;

    protected $primaryKey = 'theme_id';
}
