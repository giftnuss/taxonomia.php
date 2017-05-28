<?php

namespace Siox;

interface Storage
{
    public function retrieve($key, $version = null);

    public function store($key,$value, $version = null);

}
