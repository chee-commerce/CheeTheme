<?php namespace Chee\Theme\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';

    protected $primaryKey = 'module_id';

    public function views()
    {
        return $this->hasMany('ModuleView', 'modules_module_id', 'module_id');
    }
}
