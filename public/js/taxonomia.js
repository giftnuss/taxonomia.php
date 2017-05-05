

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

        jQuery.getJSON( "/level/",
            function ( data ) {
                var $millerCol = $("#category-miller-cols-container");

                $millerCol.millerColumn({
                   isReadOnly: true,
                   initData: data
                });
            });

    });

})(jQuery);
