<?php

require __DIR__ . '/../setup.php';

plan(4);
use_ok("Siox\Blob");
use_ok("Siox\Blob\Dba");
use_ok("Siox\Blob\Memory");
use_ok("Siox\Blob\Storage\Exception");
