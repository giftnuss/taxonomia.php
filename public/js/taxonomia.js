

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

        jQuery.getJSON( "/cty/",
            function ( data ) {
                $millerCol.millerColumn({
                   isReadOnly: true,
                   initData: data
                });
            });

        $millerCol.on("item-selected", ".miller-col-list-item",
            function (event, data) {
                jQuery.getJSON( "/cty/" + data.itemId,
                    function ( back ) {
                        console.log("Show %o",back);
                        if(back["type"] == 'category') {
                            $millerCol.millerColumn("addCol", back);
                        }
                        if(back["type"] == 'document') {
                            display_document($('#item-actions-container'),back);
                        }
                    });
        });

    });

    function display_document(element,data) {
        element.empty();
        var id = data.documentId;
        element.append("<div>" + data.documentId + "</div>");
        element.append("<div id=\"link-" + id + "\" class=\"link\">" + data.documentName + "</div>");
        $("#link-" + id).click(function () {
            console.log("hurra");
        });
        if(data.documentTitle.length > 0) {
            element.append("<div>" + data.documentTitle + "</div>");
        }
    }

})(jQuery);
