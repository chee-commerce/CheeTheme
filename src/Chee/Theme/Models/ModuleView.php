<?php namespace Chee\Theme\Models;

class ModuleView extends Eloquent
{
    protected $table = 'module_views';

    protected $primaryKey = 'module_view_id';

    public $timestamps = false;

    public function module()
    {
        return $this->belongsTo('Module', 'modules_module_id', 'module_id');
    }
}
