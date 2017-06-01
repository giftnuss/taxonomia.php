<?php

namespace Siox\Storage;

use Siox\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\AdapterInterface;

/**
 * @TODO:
 * Die Schlüssel sollten bestimmte Bedingungen erfüllen. Das wird
 * in der Klasse nicht kontrolliert sollte aber dokumentiert werden.
 */
class PlainFile implements Storage
{
     protected $versionStrategy;

     protected $filesystem;

     public function __construct(AdapterInterface $adapter, VersionStrategy $vs=null)
     {
         if($vs !== null) {
             $this->versionStrategy = $vs;
         }
         else {
             $this->versionStrategy = new VersionStrategy\Microtime;
         }
         $this->filesystem = new Filesystem($adapter);
     }

     protected function getPath($key)
     {
         $folder = substr($key,0,2);
         return "$folder/$key";
     }

     protected function headPath($key)
     {
         return "HEAD/$key";
     }

     public function has($key, $version = null)
     {
         if($version === null) {
             $head = $this->headPath($key);
             return $this->filesystem->has($head);
         }
         else {
             $path = $this->getPath($key);
             $filepath = "$path/$version";
             return $this->filesystem->has($filepath);
         }
     }

     public function store($key, $value, $version = null)
     {
         if($version === null) {
             $version = $this->versionStrategy->getVersion();
         }
         $path = $this->getPath($key);
         $filepath = "$path/$version";
         $head = $this->headPath($key);
         $this->filesystem->write($filepath,$value);
         $this->filesystem->copy($filepath,$head);
     }

     public function retrieve($key, $version = null)
     {
         if($version === null) {
             $head = $this->headPath($key);
             return $this->filesystem->read($head);
         }
         else {
             $path = $this->getPath($key);
             $filepath = "$path/$version";
             return $this->filesystem->read($filepath);
         }
     }

     public function readStream($key, $version=null)
     {

     }

     public function writeStream($key, $stream, $version = null)
     {

     }
}

