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

$tables = $setup->getTables();
is_deeply(array_keys($tables),
    ['id','concept','term','description','note','triple','uri','occurence'],
    'tables');
#print_r($tables);


$model = $setup->getModel();
is($model->getTerm($model->term('a',1)),'a');
is($model->getTerm($model->term('c',1)),'c');

$list = $db->fetchColumn("SELECT * FROM term WHERE 1=1",array(),1);
#print_r($list);
is_deeply($list,['a','b','c','a'],'term(s)');

$tt = $db->orm()->table("triple");
$rowscount = $db->sql()->countRows($tt);

$isa = $model->concept('is a');
$de = $model->concept('german');
$lang = $model->concept('language');

$model->triple($de,$isa,$lang);

is($db->sql()->countRows($tt),$rowscount,"still $rowscount triples");

$model->triple(['concept' => 'english'],['concept' => 'is a'],['concept' => 'language']);

is($db->sql()->countRows($tt),$rowscount,"still $rowscount triples 2");
