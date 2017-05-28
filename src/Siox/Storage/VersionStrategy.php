<?php

namespace Siox\Storage;

interface VersionStrategy
{
    public function getVersion($data = null);
}
