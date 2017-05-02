<?php

require __DIR__ . '/../setup.php';

plan(10);

$db = Siox\Db::factory(array(
  'driver' => 'dsn',
  'dsn' => 'sqlite::memory:'
));

isa_ok($db, 'Siox\\Db');

$setup = new Taxonomia\Setup($db);
$setup->init();

$model = $setup->getModel();
isa_ok($model->orm, 'Siox\\Db\\Orm', 'orm');
isa_ok($model->orm->table('concept'),'Siox\\Db\\Table','table concept');


$topic = $model->concept('topic');
ok($topic > 0,"return id");
$topic2 = $model->concept('topic');
is($topic,$topic2,'a concept is unique');

$uri = $model->uri("https://github.com");
ok($uri > 0,"return id");
$uri2 = $model->uri('https://github.com');
is($uri,$uri2,'an uri is unique');

$topic = $model->term('Topic',1);
ok($topic > 0,"return id");
$topic2 = $model->term('Topic',1);
is($topic,$topic2,'a term with unique arg is unique');


#$try = $model->hoppla('hallo');

ok($db->disconnect(),'disconnect');

