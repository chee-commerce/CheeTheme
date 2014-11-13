<?php namespace Chee\Theme\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class ThemeModel extends Model {

    public $table = 'active_themes';

    public $timestamps = false;

    protected $primaryKey = 'active_theme_id';
}
