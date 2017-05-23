<?php

namespace Siox\Blob;

interface Storage
{
    public function retrieve($key);

    public function store($key,$value);

}
