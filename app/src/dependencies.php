<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $app_logger = new Apix\Log\Logger\File($settings['path']);
    $app_logger->setMinLevel($settings['level'])
           ->setCascading(false)     // don't propagate to further buckets
           ->setDeferred(false);     // postpone/accumulate logs processing

    return new Apix\Log\Logger( array($app_logger) );
};
