<?php namespace Chee\Theme\Models;

class PositionView extends Eloquent
{
    protected $table = 'position_views';

    protected $primaryKey = 'position_view_id';

    public $timestamps = false;

    public function moduleView()
    {
        return $this->belongsTo('ModuleView', 'module_views_id', 'module_view_id');
    }
}
