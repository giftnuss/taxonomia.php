<?php

namespace Siox\Blob;

abstract class AbstractStorage implements Storage
{
    protected $adapter = null;

    public final function __construct($adapter = null)
    {
        $this->adapter = null;
        $this->connect();
    }

    protected function connect()
    {
        // ok
    }
}
