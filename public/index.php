<?php

if (PHP_SAPI == 'cli-server') {
    // This is required because the builtin Server behaves strange under
    // some circumctances.
    $_SERVER['SCRIPT_NAME']=basename(__FILE__);

    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

$app = require __DIR__ . '/../app/src/app.php';

// Run app
$app->run();
