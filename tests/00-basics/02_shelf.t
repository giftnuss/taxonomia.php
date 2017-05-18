<?php

require __DIR__ . '/../setup.php';

use Taxonomia\Shelf;

plan(2);

$root = __DIR__ . '/../data01';

ok(realpath($root),'testdir exists');

$shelf = new Shelf($root);

$c = $shelf->listRootDirs($root);
is_deeply($c,['a','b','c']);

$shelf->collectFolders(function ($slf,$entry) {
    print_r($entry);
    echo $slf->makeUri($entry),"\n";
    if($entry['dirname']) {
        echo $slf->makeParentUri($entry),"\n";
    }
});

$shelf->collectDocuments(function ($slf,$entry) {
    print_r($entry);
});
