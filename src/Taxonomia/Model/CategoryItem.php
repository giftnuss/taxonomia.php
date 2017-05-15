<?php

namespace Taxonomia\Model;

class CategoryItem
{
    protected $itemId;

    protected $itemName;

    protected $itemIcon;

    protected $parentId;

    protected $categoryId;

    protected $hasChildren;

    protected $numChildren;

    protected $isDeletable;

    public function __construct()
    {
        $this->hasChildren = false;

    }

    public function setId($id)
    {
        $this->itemId = $id;
    }

    public function setName($name)
    {
        $this->itemName = $name;
    }

    public function setCategoryId($id)
    {
        $this->categoryId = $id;
    }

    public function setHasChildren(bool $val)
    {
        $this->hasChildren = $val;
    }

    public function toArray()
    {
        $result = array();
        foreach((array)$this as $k => $v) {
            $result[substr($k,3)] = $v;
        }
        return $result;
    }
}


/*
    _this.setCategoryId = function (categoryId) {

        _this.categoryId = categoryId;

    };
    _this.getCategoryId = function () {
        return _this.categoryId;
    };


    _this.setHasChildren = function (hasChildren) {
        _this.hasChildren = hasChildren;
    };
    _this.getHasChildren = function () {
        return _this.hasChildren
    };

    _this.setNumChildren = function(numChildren) {
        _this.numChildren = numChildren;
        _this.setHasChildren(numChildren != 0);
    }

    _this.getNumChildren = function(){
        return _this.numChildren;
    };

    _this.isDeletable = true;
    _this.setIsDeletable = function (isDeletable) {
        _this.isDeletable = isDeletable;
    };
    _this.getIsDeletable = function () {
        return _this.isDeletable
    };


}
*/
