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
}
