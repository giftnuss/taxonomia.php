<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $app_logger = new Apix\Log\Logger\File($settings['path']);
    $app_logger->setMinLevel($settings['level'])
           ->setCascading(false)     // don't propagate to further buckets
           ->setDeferred(false);     // postpone/accumulate logs processing

    return new Apix\Log\Logger( array($app_logger) );
};

$container['db'] = function ($c) {
    $settings = $c->get('settings');

    $db = Siox\Db::factory($settings['db']);

    $setup = new Taxonomia\Setup($db);
    $setup->init(function ($setup) use ($settings) {
        $shelf = new Taxonomia\Shelf($settings['shelf']['rootdir']);
        $setup->shelf($shelf);
    });

    return $db;
};
