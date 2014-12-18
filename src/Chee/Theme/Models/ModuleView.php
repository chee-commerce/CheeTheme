<?php namespace Chee\Theme\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleView extends Model
{
    protected $table = 'module_views';

    protected $primaryKey = 'module_view_id';

    public $timestamps = false;

    public function module()
    {
        return $this->belongsTo('Module', 'modules_module_id', 'module_id');
    }
}
