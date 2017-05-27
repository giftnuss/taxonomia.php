

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
        var numeral = require("numeral");
        element.empty();
        var id = data.documentId;
        element.append("<div>" + data.documentId + "</div>");
        element.append("<div id=\"link-" + id + "\" class=\"link\">" + data.documentName + "</div>");
        $("#link-" + id).click(function () {
            var extension = data.documentName.split('.').pop().toLowerCase();
            $('.main-tabs').append("<h2>" + data.documentName + "</h2>");
            $('.main-tabs').append("<div id=\"panel-" + id + "\" class=\"panel tabbody\"></div>");
            $('#panel-' + id).append("<iframe src=\"./view/" + extension +
                "/" + id + "\" allowfullscreen webkitallowfullscreen></iframe>");

            $(".main-tabs").accessibleTabs({
                tabhead:'h2',
                fx:"show",
                fxspeed:null
            });
        });
        if(data.documentTitle.length > 0) {
            element.append("<div>" + data.documentTitle + "</div>");
        }
        element.append("<div>Size: " + numeral(data.size).format('0.00ib'));
        element.append("<div>Hash: " + data.hash);
        element.append("<div><a href=\"/document/" + id + "\" target=\"" + id + "\" >builtin pdf reader</a></div>");

    }

})(jQuery);
