<?php

namespace Taxonomia;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class Shelf
{
    protected $fs;

    public function __construct($root)
    {
        $local = new Local($root, LOCK_EX, Local::SKIP_LINKS);
        $this->fs = new Filesystem($local);
    }

    public function listRootDirs()
    {
        $result = array();
        $contents = $this->fs->listContents('',false);
        foreach($contents as $entry) {
            if($entry['type'] === 'file' ||
               $entry['basename'] === '.git') continue;
            $result[] = $entry['basename'];
        }
        return $result;
    }

    public function collectFolders(callable $func)
    {
        $contents = $this->fs->listContents('',true);
        foreach($contents as $entry) {
            if($entry['type'] === 'file' ||
               strstr($entry['path'],'.git') === $entry['path']) continue;
            $func($this,$entry);
        }
    }

    public function makeUri($entry)
    {
        $uri = sprintf("shelf:///%s%s",$entry['path'],
            ($entry['type'] === 'dir' ? '/' : ''));
        return $uri;
    }
}
