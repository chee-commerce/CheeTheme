<?php

return array(
    'path' => '/app/Themes',

    'assets' => '/themes', //=> public/themes/THEME_NAME

    'include' => array(
        'functions.php'
    ),

    'requires' => array(
        'index.php',
        'assets/screenshot.png',
        'assets/style.css',
        'theme.json' => array(
            'name',
            'version'
        ),
    )
);
