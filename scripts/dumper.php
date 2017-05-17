<?php

$debug =! true;

$autoload = require __DIR__ . '/../vendor/autoload.php';
$baseDir = dirname(__DIR__);
$autoload->add('Siox\\', array($baseDir.'/src'));

$app = require __DIR__ . "/../app/src/app.php";
$c = $app->getContainer();

$db = $c->get('db');
$model = $c->get('model');

foreach($db->fetchColumn('Select id from triple WHERE 1=1 order by id') as $id) {
    $triple = $model->getTriple($id);
    printf("%06d|%-40s | %-40s | %s\n",$id,
        sprintf("%s::%s",$triple['s']['type'],$triple['s']['value']),
        sprintf("%s::%s",$triple['p']['type'],$triple['p']['value']),
        sprintf("%s::%s",$triple['o']['type'],$triple['o']['value']));
}
