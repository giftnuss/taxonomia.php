<?php

require __DIR__ . '/../setup.php';

use Taxonomia\Shelf;

plan(2);

$root = __DIR__ . '/../data01';

ok(realpath($root),'testdir exists');

$shelf = new Shelf($root);

$c = $shelf->listRootDirs($root);
is_deeply($c,['a','b','c']);
