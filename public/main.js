
// configure requirejs
requirejs.config({
  packages: [{
    name: 'numeral',
    // This location is relative to baseUrl.
    location: '/core/js/numeral',
    main: 'numeral'
  }],
  shim: {
    'numeral': { exports: 'numeral' }
  },
  paths: {
    underscore: 'core/js/underscore',
    jquery: 'core/js/jquery.min',
    markitup: 'core/js/markitup'
  }
});

// Start the main app logic.
requirejs(['jquery','underscore'
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
    requirejs([
        'numeral',
        'wordcloud2/src/wordcloud2',
        'js/jsonTable',
        'markitup'
    ],function () {
        $(function () {
            var src = '/core/css/markitup.css';
            $("head").append($("<link rel='stylesheet' href='"+src+"' type='text/css' media='screen' />"));
        });
    });
}); }); });
