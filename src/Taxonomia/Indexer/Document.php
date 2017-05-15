<?php

namespace Taxonomia\Indexer;

use League\Flysystem\FilesystemInterface as Filesystem;

class Document
{
    protected $fs;

    protected $path;

    protected $debug = false;

    public function __construct(Filesystem $fs,$path)
    {
        $this->fs = $fs;
        $this->path = $path;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
    }
}
