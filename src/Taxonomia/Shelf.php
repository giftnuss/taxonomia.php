<?php

namespace Taxonomia;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Util\StreamHasher;

use GuzzleHttp\Psr7\Stream;

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

    public function makeUri($entry)
    {
        $uri = $this->_makeUri($entry['path']);
        $uri .= ($entry['type'] === 'dir' ? '/' : '');
        return $uri;
    }

    public function makeParentUri($entry)
    {
        $uri = $this->_makeUri($entry['dirname']);
        $uri .= "/";
        return $uri;
    }

    protected function _makeUri($entry)
    {
        $parts = preg_split('/\//u',$entry,null,PREG_SPLIT_NO_EMPTY);
        $result = array();
        foreach($parts as $part) {
            $result[] = urlencode($part);
        }
        $uri = sprintf("shelf:///%s",implode('/',$result));
        return $uri;
    }

    protected function urlToPath($url)
    {
        if(strstr($url, 'shelf:///')) {
            return urldecode(substr($url,8));
        }
        throw \Exception("Invalid url $url!");
    }

    public function getFilesize($url)
    {
        $path = $this->urlToPath($url);
        return $this->getFilesystem()->getSize($path);
    }

    public function getStream($url)
    {
        $path = $this->urlToPath($url);
        $size = $this->getFilesize($url);
        $source = $this->getFilesystem()->readStream($path);
        return new Stream($source,['size' => $size]);
    }

    public function makeHash($url)
    {
        $stream = $this->getStream($url);
        $hasher = new StreamHasher('sha1');
        return $hasher->hash($stream);
    }

}
