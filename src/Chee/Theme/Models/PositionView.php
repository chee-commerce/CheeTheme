<?php namespace Chee\Theme\Models;

use Illuminate\Database\Eloquent\Model;

class PositionView extends Model
{
    protected $table = 'position_views';

    protected $primaryKey = 'position_view_id';

    public $timestamps = false;

    public function moduleView()
    {
        return $this->belongsTo('ModuleView', 'module_views_id', 'module_view_id');
    }
}
