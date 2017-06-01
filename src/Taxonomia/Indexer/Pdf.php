<?php

namespace Taxonomia\Indexer;

use League\Flysystem\FilesystemInterface as Filesystem;

use Siox\Tempdir;
use TextCat;

class Pdf extends Document
{
    protected $tempdir;

    protected $document = 'document.pdf';

    protected $text = 'document.txt';

    protected $converter = 'pdf2txt';

    protected $tempbase = null;

    public function setTempbase($base)
    {
        $this->tempbase = $base;
    }

    public function setTextConverter($key)
    {
        $this->converter = $key;
    }

    protected function getPdfToTextCommand()
    {
        $define = array(
            'pdftotext' => "pdftotext {$this->document} {$this->text}",
            'pdf2txt' => "pdf2txt -o {$this->text} {$this->document}"
        );
        return $define[$this->converter];
    }

    protected function _prepareTempdir()
    {
        if(empty($this->tempdir)) {
            $this->tempdir = new Tempdir($this->tempbase, ['debug' => $this->debug]);
        }
        $path = realpath($this->fs->getAdapter()->getPathPrefix() .
            DIRECTORY_SEPARATOR . $this->path);

        if($this->debug) {
            $this->log('debug',"Path $path");
        }
        $this->tempdir->fetchFile($path,$this->document);
    }

    public function detectLanguages()
    {
        $this->_prepareTempdir();
        $this->tempdir->runCommand($this->getPdfToTextCommand());
        $file = $this->tempdir->getFilehandle($this->text);
        $text = fread($file , 1024 * 4);
        fclose($text);

        $textcat = new TextCat();
        return $textcat->classify( $text );
    }

    public function extractText()
    {
        $this->_prepareTempdir();
        $this->tempdir->runCommand($this->getPdfToTextCommand());
        $text = $this->tempdir->readFile($this->text);
        if($text === false) {
            throw new \Exception("Ups, can't convert {$this->path}.");
        }
        return $text;
    }
}
