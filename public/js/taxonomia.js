

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
                        $millerCol.millerColumn("addCol", back);
                    });
        });

    });

})(jQuery);
