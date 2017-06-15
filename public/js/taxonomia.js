
// please see https://www.youtube.com/watch?v=MJlOVKWBZ6w&t=3326
function hash_colors(sha1) {
    var $ = jQuery;
    var container = $('<div>');
    for(i = 0; i <3; ++i) {
        var row = $('<div style="clear:both;">');
        for(j=0; j<6; ++j) {
            var color = sha1.substr(i*2+j*6,6);
            var cell = $("<div style=\"float:left;" +
               "background-color:#" + color + ";\"></div>");
            $(cell).width("16.666%").height("3em");
            row.append(cell);
        }
        container.append(row);
    }
    return container;
}

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
                            display_tagcloud($('#word-cloud-container'),back);
                        }
                    });
        });

    });

    function display_document(element,data) {
        element.empty();
        var id = data.documentId;
        var compiled = _.template($("#document-details").html());
        element.append(compiled({
            id: data.documentId,
            name: data.documentName,
            title: data.documentTitle,
            size: data.size,
            hash: data.hash
        }));

        // Link to custom pdf viewer (WIP)
        $("#link-" + id).click(function () {
            var extension = data.documentName.split('.').pop().toLowerCase();
            $('.main-tabs').append("<h2>" + data.documentName + "</h2>");
            $('.main-tabs').append("<div id=\"panel-" + id + "\" class=\"panel tabbody\"></div>");
            $('#panel-' + id).append("<iframe src=\"./view/" + extension +
                "/" + id + "\" allowfullscreen></iframe>");

            $(".main-tabs").accessibleTabs({
                tabhead:'h2',
                fx:"show",
                fxspeed:null
            });
        });

        // Link to text view
        $('#text-' + id).click(function () {
            $('.main-tabs').append("<h2>Text " + id + "</h2>");
            $('.main-tabs').append("<div id=\"text-panel-" + id + "\" class=\"panel tabbody\"></div>");
            $('#text-panel-' + id).append("<iframe src=\"./view/text/" + id + "\" allowfullscreen></iframe>");

            $(".main-tabs").accessibleTabs({
                tabhead:'h2',
                fx:"show",
                fxspeed:null
            });
        });

        element.append(hash_colors(data.hash));
    }

    function display_tagcloud(element,data)
    {
        var id = data.documentId;
        element.empty();

        var compiled = _.template($("#tagcloud-details").html());
        var html = compiled({});
        element.append(html);
        element.accessibleTabs({
                tabhead:'h3',
                fx:"show",
                fxspeed:null,
                // important to distinguish the tabs - but needs additional styling work
                tabsListClass: 'inner-tabs-list',
                tabbody: '.inner-tabbody'
        });

        var canvas = $('canvas',element);
        var wordlist = $('.wordlist');
        jQuery.getJSON( "./view/cloud/" + id,
            function ( back ) {
                WordCloud(canvas[0], { list: back } );
                var table = $('<table>');
                table.jsonTable({
                    head: ['word','count'],
                    json: [0,1]
                });
                table.jsonTableUpdate({source: back});
                wordlist.append(table);
            });
    }

})(jQuery);
