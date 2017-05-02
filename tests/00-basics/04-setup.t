<?php

require __DIR__ . '/../setup.php';

plan(10);

$db = Siox\Db::factory(array(
  'driver' => 'dsn',
  'dsn' => 'sqlite::memory:'
));

$setup = new Taxonomia\Setup($db);
$setup->init(function ($setup) {
    $shelf = new Taxonomia\Shelf(realpath(__DIR__ . '/../data01'));
    $setup->shelf($shelf);
});

$model = $setup->getModel();
is($model->getTerm($model->term('a',1)),'a');
is($model->getTerm($model->term('c',1)),'c');
