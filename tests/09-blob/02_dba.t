<?php

require __DIR__ . '/../setup.php';

plan(1);

$adapter['default'] = new Siox\Blob\Dba();
$adapter['array'] = new Siox\Blob\Dba([tempnam(null,'test_dba'),'gdbm','wd']);

#print_r($adapter);
ok(1);
