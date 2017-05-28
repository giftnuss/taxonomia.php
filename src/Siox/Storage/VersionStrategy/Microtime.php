<?php

namespace Siox\Storage\VersionStrategy;

use Datetime;
use Siox\Storage\VersionStrategy;

class Microtime implements VersionStrategy
{
    public function getVersion($data = null)
    {
        $d=explode(" ",microtime());
        $n=number_format($d[1]+$d[0],8,".","");
        $c=base_convert($n,10,36);
        return strrev($c);
    }

    public function toDatetime($str)
    {
        $b36 = strrev($str);
        $b10 = base_convert($b36,36,10);

        $time = substr($b10,0,10);
        return new Datetime("@$time");
    }

    public function toMicrotime($str)
    {
        $b36 = strrev($str);
        $b10 = base_convert($b36,36,10);

        return "0." . substr($b10,10) . " " . substr($b10,0,10);
    }
}
