<?php

return array(
    'path' => 'themes',

    'assets' => '/themes', //=> public/themes/THEME_NAME

    'include' => array(
        'helpers.php',
        'bindings.php',
        'observers.php',
        'filters.php',
        'composers.php',
        'routes.php'
    ),

    'requires' => array(
        'ServiceProvider.php',
        'theme.json' => array(
            'name',
            'version',
            'icon'
        ),
    )
);
