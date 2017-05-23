<?php

namespace Siox;

use Siox\Blob\Storage;

class Blob implements Storage
{
    protected $storage;

    public function __construct(Storage $storage=null)
    {
        if($storage !== null) {
            $this->setStorage( $storage );
        }
    }

    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function retrieve($key)
    {
        return $this->storage->retrieve($key);
    }

    public function store($key,$value)
    {
        return $this->storage->store($key,$value);
    }

}
