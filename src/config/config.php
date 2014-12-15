<?php

return array(
    //use CheeModule for?
    'systemName' => 'CheeCommerce',

    //Version of system like 4.5.2
    'sysVersion' => CH_VERSION,

    //Major version of system like 4
    'sysMajorVersion' => CH_MAJOR_VERSION,

    //Major version of system like 5
    'sysMinorVersion' => CH_MINOR_VERSION,

    //Major version of system like 2
    'sysPathVersion' => CH_PATH_VERSION,

    //Name of configuration file in every module by json format
    'configFile' => '/theme.json',

    //path of module in app directory
    'path' => 'themes',

    //assets in public directory
    'assets' => 'themes',

    //include common files
    'include' => array(
        'helpers.php',
        'bindings.php',
        'observers.php',
        'filters.php',
        'composers.php',
        'routes.php'
    ),

    //required files for install a module
    'requires' => array(
        'theme.json' => array(
            'name',
            'version'
        )
    ),
);
