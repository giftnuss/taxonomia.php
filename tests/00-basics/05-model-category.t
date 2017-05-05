<?php

require __DIR__ . '/../setup.php';

use Taxonomia\Model\Category;
use Taxonomia\Model\CategoryItem;

$c = new Category();
$i = new CategoryItem();
$c->addItem($i);

$json = <<<__JSON__
{
    "categoryId": null,
    "categoryName": null,
    "parentId": null,
    "isLowestLevel": false,
    "items": [
        {
            "itemId": null,
            "itemName": null,
            "itemIcon": null,
            "parentId": null,
            "categoryId": null,
            "hasChildren": null,
            "numChildren": null,
            "isDeletable": null
        }
    ]
}
__JSON__;

is_deeply(json_decode(json_encode($c)),json_decode($json),'empty object');


#echo json_encode($c,JSON_PRETTY_PRINT);

#print_r($c->jsonSerialize());


