<?php

if(!isset($GLOBALS['__setup_done'])) {
  $__setup_done = true;
  $loader = require realpath(__DIR__.'/../vendor/autoload.php');
  $loader->add('Siox\\', array(__DIR__.'/../src'));

  #print_r(get_class_methods($loader));
  require realpath(__DIR__.'/../vendor/giftnuss/test.php/Test.php');

  $baseDir = dirname(__DIR__);

  #$loader->add('Anagrom\\', array($baseDir.'/sample/anagrom/src'));

  $tempdir = __DIR__.'/temp';

  if (!is_dir($tempdir)) {
    if (!mkdir($tempdir)) {
        die("$tempdir not created");
    }
  }
}

# some cleanup is required when all tests are run in one process
unset($expect);
unset($name);
unset($names);
