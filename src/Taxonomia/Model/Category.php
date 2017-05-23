<?php

namespace Taxonomia\Model;

use JsonSerializable;

class Category implements JsonSerializable
{
    protected $categoryId;

    protected $categoryName;

    protected $parentId;

    protected $isLowestLevel;

    protected $items = array();

    public function __construct()
    {
        $this->isLowestLevel = false;
    }

    public function setId($id)
    {
        $this->categoryId = $id;
    }

    public function setName($name)
    {
        $this->categoryName = $name;
    }

    public function setParentId($id)
    {
        $this->parentId = $id;
    }

    public function isLowestLevel(bool $val = null)
    {
        if($val === null) {
            return $this->isLowestLevel;
        }
        else {
            $this->isLowestLevel = $val;
        }
    }

    public function addItem(CategoryItem $item)
    {
        $this->items[] = $item;
    }

    // Note to myself - (array) cast marks protected members with a star
    // I do not know how to avoid this.
    public function jsonSerialize()
    {
        $result = array(
            'type' => 'category',
            'categoryId' => $this->categoryId,
            'categoryName' => $this->categoryName,
            'parentId' => $this->parentId,
            'isLowestLevel' => $this->isLowestLevel,
            'items' => []
        );
        foreach($this->items as $item) {
            $result['items'][] = $item->toArray();
        }
        return $result;
    }
}
