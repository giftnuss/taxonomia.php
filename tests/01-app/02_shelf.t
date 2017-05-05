<?php

require __DIR__ . '/../setup.php';

use Taxonomia\Shelf;


// Instantiate the app
$settings = require __DIR__ . '/../../app/src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../../app/src/dependencies.php';

$container = $app->getContainer();

$db = $container->get('db');
