
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
    jquery: 'core/js/jquery.min'
  }
});

// Start the main app logic.
requirejs(['jquery','underscore'
], function () {
    requirejs(['miller/js/miller','js/jQuery.tabs','numeral'
  ], function () {
      requirejs(['js/taxonomia','wordcloud2/src/wordcloud2.js'
    ], function () {

    $(document).ready(function(){
        $(".main-tabs").accessibleTabs({
            tabhead:'h2',
            fx:"show",
            fxspeed:null
        });
    });

}); }); });
