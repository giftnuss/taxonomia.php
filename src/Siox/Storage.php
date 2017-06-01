<?php

namespace Siox;

interface Storage
{
    public function has($key, $version = null);

    public function retrieve($key, $version = null);

    public function store($key,$value, $version = null);

    public function readStream($key, $version = null);

    public function writeStream($key, $stream, $version = null);

}
