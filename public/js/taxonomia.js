

function showSpinner(){
    var spinner = $("<div/>").append($("<div/>").addClass("spinner"));
    spinner.addClass("spinner-wrapper");
    $("body").append(spinner);
}

function hideSpinner(){
    $(".spinner-wrapper").remove();
}

(function ($) {

    $(document).ready(function () {

        $( document ).ajaxSend(function() {
            showSpinner();
        });
        $( document ).ajaxStop(function() {
            hideSpinner();
        });

        var $millerCol = $("#category-miller-cols-container");

        jQuery.getJSON( "/level/",
            function ( data ) {
                $millerCol.millerColumn({
                   isReadOnly: true,
                   initData: data
                });
            });

$millerCol.on("item-selected", ".miller-col-list-item",
                    function (event, data) {

            console.log("item selected.. data: " + data.itemId);
/*
            var category = findCategoryByParentId(categories, data.categoryId);
            var itemCategories2 = itemCategories.find({
                $and: [
                    {
                        categoryId: category.getCategoryId()
                    },
                    {
                        parentId: data.itemId
                    }
               ]
            });

            category.items = itemCategories2;

            $millerCol.millerColumn("addCol", category);
*/
        });

    });

})(jQuery);
