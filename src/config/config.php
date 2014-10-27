<?php

return array(
    'path' => 'themes',

    'assets' => '/themes', //=> public/themes/THEME_NAME

    'include' => array(
        'functions.php'
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
