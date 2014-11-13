<?php namespace Chee\Theme\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class ThemePosition extends Model {

    public $table = 'theme_positions';

    public $timestamps = false;

    protected $primaryKey = 'theme_position_id';
}
