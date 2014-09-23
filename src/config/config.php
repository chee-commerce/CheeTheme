<?php

return array(
    'path' => '/app/Themes',

    'assets' => '/themes', //=> public/themes/THEME_NAME

    'requires' => array(
        'index.php',
        'screenshot.png',
        'theme.json' => array(
            'name',
            'version'
        ),
    )
);
