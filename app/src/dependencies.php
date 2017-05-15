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

$container['shelf'] = function ($c) {
    $settings = $c->get('settings');

    $shelf = new Taxonomia\Shelf($settings['shelf']['rootdir']);
    return $shelf;
};

$container['db'] = function ($c) {
    $settings = $c->get('settings');

    $db = Siox\Db::factory($settings['db']);

    $setup = new Taxonomia\Setup($db);
    $setup->init(function ($setup) use ($c) {
        $shelf = $c->get('shelf');
        $setup->shelf($shelf);
    });

    return $db;
};

$container['model'] = function ($c) {
    $db = $c->get('db');
    $schema = new Taxonomia\Schema();
    return new Taxonomia\Model($db,$schema);
};
