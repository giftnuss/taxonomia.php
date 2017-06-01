<?php

namespace Taxonomia\Indexer;

use League\Flysystem\FilesystemInterface as Filesystem;

class Document
{
    protected $fs;

    protected $path;

    protected $debug = false;

    protected $logger;

    public function __construct(Filesystem $fs,$path)
    {
        $this->fs = $fs;
        $this->path = $path;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    protected function log($level,$msg)
    {
        if(isset($this->logger)) {
            $this->logger->$level($msg);
        }
        else {
            error_log($msg);
        }
    }
}
