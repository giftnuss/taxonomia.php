<?php

namespace Taxonomia\Model;

use JsonSerializable;

class Document implements JsonSerializable
{
    protected $documentId;

    protected $documentName;

    protected $documentTitle = '';

    protected $documentHash = null;

    public function setId($id)
    {
        $this->documentId = $id;
    }

    public function setName($name)
    {
        $this->documentName = $name;
    }

    public function setTitle($title)
    {
        $this->documentTitle = $title;
    }

    public function setSize($size)
    {
        $this->documentSize = $size;
    }

    public function setHash($hash)
    {
        $this->documentHash = $hash;
    }

    public function jsonSerialize()
    {
        $result = array(
            'type' => 'document',
            'documentId' => $this->documentId,
            'documentName' => $this->documentName,
            'documentTitle' => $this->documentTitle,
            'size' => $this->documentSize
        );
        if($this->documentHash !== null) {
            $result['hash'] = $this->documentHash;
        }
        return $result;
    }
}
