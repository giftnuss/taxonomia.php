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

    public function detectLanguages()
    {
        if(empty($this->tempdir)) {
            $this->tempdir = new Tempdir(null, ['debug' => $this->debug]);
        }
        $path = realpath($this->fs->getAdapter()->getPathPrefix() .
            DIRECTORY_SEPARATOR . $this->path);

        $this->tempdir->fetchFile($path,$this->document);

        $this->tempdir->runCommand("pdftotext {$this->document} {$this->text}");
        $file = $this->tempdir->getFilehandle($this->text);
        $text = fread($file , 1024 * 4);

        $textcat = new TextCat();
        return $textcat->classify( $text );
    }
}
