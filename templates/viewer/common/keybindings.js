
define(function () {

    return function (viewer) {
        window.addEventListener('keydown', function ( evt ) {
            var key      = evt.keyCode,
                shiftKey = evt.shiftKey;

            // blanked-out mode?
            if ( viewer.isBlankedOut() ) {
                switch ( key ) {
                    case 16: // Shift
                    case 17: // Ctrl
                    case 18: // Alt
                    case 91: // LeftMeta
                    case 93: // RightMeta
                    case 224: // MetaInMozilla
                    case 225: // AltGr
                        // ignore modifier keys alone
                        break;
                    default:
                        viewer.leaveBlankOut();
                        break;
                }
            } else {
                switch ( key ) {
                    case 8: // backspace
                    case 33: // pageUp
                    case 37: // left arrow
                    case 38: // up arrow
                    case 80: // key 'p'
                        viewer.showPreviousPage();
                        break;
                    case 13: // enter
                    case 34: // pageDown
                    case 39: // right arrow
                    case 40: // down arrow
                    case 78: // key 'n'
                        viewer.showNextPage();
                        break;
                    case 32: // space
                        shiftKey ? viewer.showPreviousPage() : viewer.showNextPage();
                        break;
                    case 66:  // key 'b' blanks screen (to black) or returns to the document
                    case 190: // and so does the key '.' (dot)
                        if ( viewer.isPresentationMode() ) {
                            viewer.blankOut();
                        }
                        break;
                    case 87:  // key 'w' blanks page (to white) or returns to the document
                    case 188: // and so does the key ',' (comma)
                        if ( viewer.isPresentationMode() ) {
                            viewer.blankOut('#FFF');
                        }
                        break;
                    case 36: // key 'Home' goes to first page
                        viewer.showPage(1);
                        break;
                    case 35: // key 'End' goes to last page
                        viewer.showPage(pages.length);
                        break;
                }
            }
        });
    }
});
