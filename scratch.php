<?php


$loader = require realpath(__DIR__.'/vendor/autoload.php');

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

$local = new Local('.', LOCK_EX, Local::SKIP_LINKS););
$fs = new Filesystem($local);

print_r($fs->listContents('',true));
