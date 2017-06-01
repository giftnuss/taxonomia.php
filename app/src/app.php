<?php

$basedir = realpath(__DIR__ . '/../..');
$autoload = require "$basedir/vendor/autoload.php";
$autoload->add('Siox\\',array($basedir."/src"));

session_start();

// Instantiate the app
$settings = require __DIR__ . '/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/dependencies.php';

// Register middleware
require __DIR__ . '/middleware.php';

// Register routes
require __DIR__ . '/routes.php';

return $app;
