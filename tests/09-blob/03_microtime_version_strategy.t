<?php

require __DIR__ . '/../setup.php';

plan(2);

$m = new Siox\Storage\VersionStrategy\Microtime;

$u = microtime();
$version = $m->getVersion();
$now = new Datetime();

is(strlen($version),12,"length=12");
is($m->toDatetime($version)->format(Datetime::W3C),
   $now->format(Datetime::W3C),"toDatetime");

diag("$u ~~ " . $m->toMicrotime($version));
