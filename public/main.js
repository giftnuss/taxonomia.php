
// Start the main app logic.
requirejs(['miller/js/jquery',
], function () {
    requirejs(['miller/js/miller','js/jQuery.tabs'
  ], function () {
      requirejs(['js/taxonomia'
    ], function () {

    $(document).ready(function(){
        $(".main-tabs").accessibleTabs({
            tabhead:'h2',
            fx:"show",
            fxspeed:null
        });
    });
    $(function () {
        var num = 1;
        $('#word-cloud-container').click(function (evt) {
            $('.main-tabs').append("<h2>Tab-" + num++ + "</h2>");
            $('.main-tabs').append("<div class=\"panel tabbody\">TEXT</div>");
            $(".main-tabs").accessibleTabs({
                tabhead:'h2',
                fx:"show",
                fxspeed:null
            });
        });
    });

}); }); });
