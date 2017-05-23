<?php

namespace Taxonomia\Model;

use JsonSerializable;

class Document implements JsonSerializable
{
    protected $documentId;

    protected $documentName;

    protected $documentTitle = '';

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

    public function jsonSerialize()
    {
        $result = array(
            'type' => 'document',
            'documentId' => $this->documentId,
            'documentName' => $this->documentName,
            'documentTitle' => $this->documentTitle
        );

        return $result;
    }
}
