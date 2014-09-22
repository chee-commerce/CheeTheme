<?php namespace Chee\Theme\Facades;

use Illuminate\Support\Facades\Facade;

class CheeTheme extends Facade {

    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'chee-theme';
    }
}
