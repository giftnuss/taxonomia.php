<?php

namespace Taxonomia;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class Shelf
{
    protected $fs;

    protected $skipDocumentExtension = array();

    public function __construct($root)
    {
        $local = new Local($root, LOCK_EX, Local::SKIP_LINKS);
        $this->fs = new Filesystem($local);

        $this->skipDocumentExtension = array(
            'js' => '', 'css' => '',
            'png' => '', 'jpg' => '', 'jpeg' => '', 'gif' => ''
        );
    }

    public function getFilesystem()
    {
        return $this->fs;
    }

    public function addSkipDocumentExtension(array $arg)
    {
        foreach($arg as $ext) {
            $this->skipDocumentExtension[strtolower($ext)] = '';
        }
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

    public function collectFolders(callable $func)
    {
        $contents = $this->fs->listContents('',true);
        foreach($contents as $entry) {
            if($entry['type'] === 'file' ||
               strstr($entry['path'],'.git') === $entry['path']) continue;
            $func($this,$entry);
        }
    }

    public function collectDocuments(callable $func)
    {
        $dirs = $this->listRootDirs();
        foreach($dirs as $dir) {
            $contents = $this->fs->listContents($dir,true);
            foreach($contents as $entry) {
                if($entry['type'] !== 'file') continue;
                if(isset($entry['extension']) &&
                    isset($this->skipDocumentExtension[
                       strtolower($entry['extension'])
                    ])) continue;
                $func($this,$entry);
            }
        }
    }
    /**
     * UTF-8 to uri encode - is there a better way?
     */
    public function makeUri($entry)
    {
        $parts = preg_split('/\//u',$entry['path'],null,PREG_SPLIT_NO_EMPTY);
        $result = array();
        foreach($parts as $part) {
            /*
            $str = '';
            $chars = preg_split('//u', $part, null, PREG_SPLIT_NO_EMPTY));
            foreach($chars as $char) {
                if(strlen($char) === 1) {
                    $str .= rawurlencode($char);
                }
                else {
                    $len = strlen($char);
                    for($i = 0; $i < $len; ++$i) {
                        $str .= sprintf("%%%02x",substr($char,$i,1));
                    }
                }
            }
            */
            $result[] = urlencode($part);
        }
        $uri = sprintf("shelf:///%s%s",implode('/',$result),
            ($entry['type'] === 'dir' ? '/' : ''));
        return $uri;
    }
}
