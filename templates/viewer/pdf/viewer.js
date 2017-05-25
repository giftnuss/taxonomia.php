
/*global document, window*/

function Viewer( viewerPlugin, parameters ) {
    "use strict";

    var self               = this,
        kScrollbarPadding  = 40,
        kMinScale          = 0.25,
        kMaxScale          = 4.0,
        kDefaultScaleDelta = 1.1,
        kDefaultScale      = 'auto',
        presentationMode   = false,
        isFullScreen       = false,
        initialized        = false,
        url,
        viewerElement      = document.getElementById('viewer'),
        canvasContainer    = document.getElementById('canvasContainer'),
        overlayNavigator   = document.getElementById('overlayNavigator'),
        titlebar           = document.getElementById('titlebar'),
        toolbar            = document.getElementById('toolbarContainer'),
        pageSwitcher       = document.getElementById('toolbarLeft'),
        zoomWidget         = document.getElementById('toolbarMiddleContainer'),
        scaleSelector      = document.getElementById('scaleSelect'),
        dialogOverlay      = document.getElementById('dialogOverlay'),
        toolbarRight       = document.getElementById('toolbarRight'),
        pages              = [],
        currentPage,
        scaleChangeTimer,
        touchTimer,
        toolbarTouchTimer,
        UI_FADE_DURATION   = 5000;

    function isBlankedOut() {
        return (blanked.style.display === 'block');
    }

    function selectScaleOption( value ) {
        // Retrieve the options from the zoom level <select> element
        var options              = scaleSelector.options,
            option,
            predefinedValueFound = false,
            i;

        for ( i = 0; i < options.length; i += 1 ) {
            option = options[i];
            if ( option.value !== value ) {
                option.selected = false;
                continue;
            }
            option.selected      = true;
            predefinedValueFound = true;
        }
        return predefinedValueFound;
    }

    function getPages() {
        return viewerPlugin.getPages();
    }

    function setScale( val, resetAutoSettings ) {
        if ( val === self.getZoomLevel() ) {
            return;
        }

        self.setZoomLevel(val);

        var event = document.createEvent('UIEvents');
        event.initUIEvent('scalechange', false, false, window, 0);
        event.scale             = val;
        event.resetAutoSettings = resetAutoSettings;
        window.dispatchEvent(event);
    }

    function onScroll() {
        var pageNumber;

        if ( viewerPlugin.onScroll ) {
            viewerPlugin.onScroll();
        }
        if ( viewerPlugin.getPageInView ) {
            pageNumber = viewerPlugin.getPageInView();
            if ( pageNumber ) {
                currentPage                                 = pageNumber;
                document.getElementById('pageNumber').value = pageNumber;
            }
        }
    }

    function delayedRefresh( milliseconds ) {
        window.clearTimeout(scaleChangeTimer);
        scaleChangeTimer = window.setTimeout(function () {
            onScroll();
        }, milliseconds);
    }

    function parseScale( value, resetAutoSettings ) {
        var scale,
            maxWidth,
            maxHeight;

        if ( value === 'custom' ) {
            scale = parseFloat(document.getElementById('customScaleOption').textContent) / 100;
        } else {
            scale = parseFloat(value);
        }

        if ( scale ) {
            setScale(scale, true);
            delayedRefresh(300);
            return;
        }

        maxWidth  = canvasContainer.clientWidth - kScrollbarPadding;
        maxHeight = canvasContainer.clientHeight - kScrollbarPadding;

        switch ( value ) {
            case 'page-actual':
                setScale(1, resetAutoSettings);
                break;
            case 'page-width':
                viewerPlugin.fitToWidth(maxWidth);
                break;
            case 'page-height':
                viewerPlugin.fitToHeight(maxHeight);
                break;
            case 'page-fit':
                viewerPlugin.fitToPage(maxWidth, maxHeight);
                break;
            case 'auto':
                if ( viewerPlugin.isSlideshow() ) {
                    viewerPlugin.fitToPage(maxWidth + kScrollbarPadding, maxHeight + kScrollbarPadding);
                } else {
                    viewerPlugin.fitSmart(maxWidth);
                }
                break;
        }

        selectScaleOption(value);
        delayedRefresh(300);
    }

    function readZoomParameter( zoom ) {
        var validZoomStrings = ["auto", "page-actual", "page-width"],
            number;

        if ( validZoomStrings.indexOf(zoom) !== -1 ) {
            return zoom;
        }
        number = parseFloat(zoom);
        if ( number && kMinScale <= number && number <= kMaxScale ) {
            return zoom;
        }
        return kDefaultScale;
    }

    function readStartPageParameter( startPage ) {
        var result = parseInt(startPage, 10);
        return isNaN(result) ? 1 : result;
    }

    this.initialize = function () {
        var initialScale;

        initialScale = readZoomParameter(parameters.zoom);

        url                    = parameters.documentUrl;
        document.title         = parameters.title;
        var documentName       = document.getElementById('documentName');
        documentName.innerHTML = "";
        documentName.appendChild(documentName.ownerDocument.createTextNode(parameters.title));

        viewerPlugin.onLoad = function () {

            if ( viewerPlugin.isSlideshow() ) {
                // Slideshow pages should be centered
                canvasContainer.classList.add("slideshow");
                // Show page nav controls only for presentations
                pageSwitcher.style.visibility = 'visible';
            } else {
                // For text documents, show the zoom widget.
                zoomWidget.style.visibility = 'visible';
                // Only show the page switcher widget if the plugin supports page numbers
                if ( viewerPlugin.getPageInView ) {
                    pageSwitcher.style.visibility = 'visible';
                }
            }

            initialized                                   = true;
            pages                                         = getPages();
            document.getElementById('numPages').innerHTML = 'of ' + pages.length;

            self.showPage(readStartPageParameter(parameters.startpage));

            // Set default scale
            parseScale(initialScale);

            canvasContainer.onscroll = onScroll;
            delayedRefresh();
            // Doesn't work in older browsers: document.getElementById('loading-document').remove();
            var loading = document.getElementById('loading-document');
            loading.parentNode.removeChild(loading);
        };

        viewerPlugin.initialize(canvasContainer, url);
    };

    /**
     * Shows the 'n'th page. If n is larger than the page count,
     * shows the last page. If n is less than 1, shows the first page.
     * @return {undefined}
     */
    this.showPage = function ( n ) {
        if ( n <= 0 ) {
            n = 1;
        } else if ( n > pages.length ) {
            n = pages.length;
        }

        viewerPlugin.showPage(n);

        currentPage                                 = n;
        document.getElementById('pageNumber').value = currentPage;
    };

    /**
     * Shows the next page. If there is no subsequent page, does nothing.
     * @return {undefined}
     */
    this.showNextPage = function () {
        self.showPage(currentPage + 1);
    };

    /**
     * Shows the previous page. If there is no previous page, does nothing.
     * @return {undefined}
     */
    this.showPreviousPage = function () {
        self.showPage(currentPage - 1);
    };

    /**
     * Attempts to 'download' the file.
     * @return {undefined}
     */
    this.download = function () {
        var documentUrl = url.split('#')[0];
        if ( documentUrl.indexOf('?') !== -1 ) {
            documentUrl += '&contentDispositionType=attachment';
        } else {
            documentUrl += '?contentDispositionType=attachment';
        }
        window.open(documentUrl, '_parent');
    };

    /**
     * Prints the document canvas.
     * @return {undefined}
     */
    this.printDocument = function () {
        window.print();
    };

    /**
     * Toggles the fullscreen state of the viewer
     * @return {undefined}
     */
    this.toggleFullScreen = function () {
        var elem = viewerElement;
        if ( !isFullScreen ) {
            if ( elem.requestFullscreen ) {
                elem.requestFullscreen();
            } else if ( elem.mozRequestFullScreen ) {
                elem.mozRequestFullScreen();
            } else if ( elem.webkitRequestFullscreen ) {
                elem.webkitRequestFullscreen();
            } else if ( elem.webkitRequestFullScreen ) {
                elem.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
            } else if ( elem.msRequestFullscreen ) {
                elem.msRequestFullscreen();
            }
        } else {
            if ( document.exitFullscreen ) {
                document.exitFullscreen();
            } else if ( document.cancelFullScreen ) {
                document.cancelFullScreen();
            } else if ( document.mozCancelFullScreen ) {
                document.mozCancelFullScreen();
            } else if ( document.webkitExitFullscreen ) {
                document.webkitExitFullscreen();
            } else if ( document.webkitCancelFullScreen ) {
                document.webkitCancelFullScreen();
            } else if ( document.msExitFullscreen ) {
                document.msExitFullscreen();
            }
        }
    };

    /**
     * Toggles the presentation mode of the viewer.
     * Presentation mode involves fullscreen + hidden UI controls
     */
    this.togglePresentationMode = function () {
        var overlayCloseButton = document.getElementById('overlayCloseButton');

        if ( !presentationMode ) {
            titlebar.style.display = toolbar.style.display = 'none';
            overlayCloseButton.style.display = 'block';
            canvasContainer.classList.add('presentationMode');
            canvasContainer.onmousedown   = function ( event ) {
                event.preventDefault();
            };
            canvasContainer.oncontextmenu = function ( event ) {
                event.preventDefault();
            };
            canvasContainer.onmouseup     = function ( event ) {
                event.preventDefault();
                if ( event.which === 1 ) {
                    self.showNextPage();
                } else {
                    self.showPreviousPage();
                }
            };
            parseScale('page-fit');
        } else {
            if ( isBlankedOut() ) {
                leaveBlankOut();
            }
            titlebar.style.display = toolbar.style.display = 'block';
            overlayCloseButton.style.display = 'none';
            canvasContainer.classList.remove('presentationMode');
            canvasContainer.onmouseup     = function () {
            };
            canvasContainer.oncontextmenu = function () {
            };
            canvasContainer.onmousedown   = function () {
            };
            parseScale('auto');
        }

        presentationMode = !presentationMode;
    };

    /**
     * Gets the zoom level of the document
     * @return {!number}
     */
    this.getZoomLevel = function () {
        return viewerPlugin.getZoomLevel();
    };

    /**
     * Set the zoom level of the document
     * @param {!number} value
     * @return {undefined}
     */
    this.setZoomLevel = function ( value ) {
        viewerPlugin.setZoomLevel(value);
    };

    /**
     * Zoom out by 10 %
     * @return {undefined}
     */
    this.zoomOut = function () {
        // 10 % decrement
        var newScale = (self.getZoomLevel() / kDefaultScaleDelta).toFixed(2);
        newScale     = Math.max(kMinScale, newScale);
        parseScale(newScale, true);
    };

    /**
     * Zoom in by 10%
     * @return {undefined}
     */
    this.zoomIn = function () {
        // 10 % increment
        var newScale = (self.getZoomLevel() * kDefaultScaleDelta).toFixed(2);
        newScale     = Math.min(kMaxScale, newScale);
        parseScale(newScale, true);
    };

    function cancelPresentationMode() {
        if ( presentationMode && !isFullScreen ) {
            self.togglePresentationMode();
        }
    }

    function handleFullScreenChange() {
        isFullScreen = !isFullScreen;
        cancelPresentationMode();
    }

    function showOverlayNavigator() {
        if ( presentationMode || viewerPlugin.isSlideshow() ) {
            overlayNavigator.className = 'viewer-touched';
            window.clearTimeout(touchTimer);
            touchTimer = window.setTimeout(function () {
                overlayNavigator.className = '';
            }, UI_FADE_DURATION);
        }
    }

    /**
     */
    function showToolbars() {
        titlebar.classList.add('viewer-touched');
        toolbar.classList.add('viewer-touched');
        window.clearTimeout(toolbarTouchTimer);
        toolbarTouchTimer = window.setTimeout(function () {
            hideToolbars();
        }, UI_FADE_DURATION);
    }

    function hideToolbars() {
        titlebar.classList.remove('viewer-touched');
        toolbar.classList.remove('viewer-touched');
    }

    function toggleToolbars() {
        if ( titlebar.classList.contains('viewer-touched') ) {
            hideToolbars();
        } else {
            showToolbars();
        }
    }

    function blankOut( value ) {
        blanked.style.display         = 'block';
        blanked.style.backgroundColor = value;
        hideToolbars();
    }

    function leaveBlankOut() {
        blanked.style.display = 'none';
        toggleToolbars();
    }

    function setButtonClickHandler( buttonId, handler ) {
        var button = document.getElementById(buttonId);

        button.addEventListener('click', function () {
            handler();
            button.blur();
        });
    }

    function init() {

        if ( viewerPlugin ) {
            self.initialize();

            if ( !(document.exitFullscreen || document.cancelFullScreen || document.mozCancelFullScreen || document.webkitExitFullscreen || document.webkitCancelFullScreen || document.msExitFullscreen) ) {
                document.getElementById('fullscreen').style.visibility   = 'hidden';
                document.getElementById('presentation').style.visibility = 'hidden';
            }

            setButtonClickHandler('overlayCloseButton', self.toggleFullScreen);
            setButtonClickHandler('fullscreen', self.toggleFullScreen);
            setButtonClickHandler('print', self.printDocument);
            setButtonClickHandler('presentation', function () {
                if ( !isFullScreen ) {
                    self.toggleFullScreen();
                }
                self.togglePresentationMode();
            });

            document.addEventListener('fullscreenchange', handleFullScreenChange);
            document.addEventListener('webkitfullscreenchange', handleFullScreenChange);
            document.addEventListener('mozfullscreenchange', handleFullScreenChange);
            document.addEventListener('MSFullscreenChange', handleFullScreenChange);

            setButtonClickHandler('download', self.download);

            setButtonClickHandler('zoomOut', self.zoomOut);
            setButtonClickHandler('zoomIn', self.zoomIn);

            setButtonClickHandler('previous', self.showPreviousPage);
            setButtonClickHandler('next', self.showNextPage);

            setButtonClickHandler('previousPage', self.showPreviousPage);
            setButtonClickHandler('nextPage', self.showNextPage);

            document.getElementById('pageNumber').addEventListener('change', function () {
                self.showPage(this.value);
            });

            document.getElementById('scaleSelect').addEventListener('change', function () {
                parseScale(this.value);
            });

            canvasContainer.addEventListener('click', showOverlayNavigator);
            overlayNavigator.addEventListener('click', showOverlayNavigator);
            canvasContainer.addEventListener('click', toggleToolbars);
            titlebar.addEventListener('click', showToolbars);
            toolbar.addEventListener('click', showToolbars);

            window.addEventListener('scalechange', function ( evt ) {
                var customScaleOption    = document.getElementById('customScaleOption'),
                    predefinedValueFound = selectScaleOption(String(evt.scale));

                customScaleOption.selected = false;

                if ( !predefinedValueFound ) {
                    customScaleOption.textContent = Math.round(evt.scale * 10000) / 100 + '%';
                    customScaleOption.selected    = true;
                }
            }, true);

            window.addEventListener('resize', function () {
                if ( initialized &&
                    (document.getElementById('pageWidthOption').selected ||
                    document.getElementById('pageAutoOption').selected) ) {
                    parseScale(document.getElementById('scaleSelect').value);
                }
                showOverlayNavigator();
            });

            window.addEventListener('keydown', function ( evt ) {
                var key      = evt.keyCode,
                    shiftKey = evt.shiftKey;

                // blanked-out mode?
                if ( isBlankedOut() ) {
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
                            leaveBlankOut();
                            break;
                    }
                } else {
                    switch ( key ) {
                        case 8: // backspace
                        case 33: // pageUp
                        case 37: // left arrow
                        case 38: // up arrow
                        case 80: // key 'p'
                            self.showPreviousPage();
                            break;
                        case 13: // enter
                        case 34: // pageDown
                        case 39: // right arrow
                        case 40: // down arrow
                        case 78: // key 'n'
                            self.showNextPage();
                            break;
                        case 32: // space
                            shiftKey ? self.showPreviousPage() : self.showNextPage();
                            break;
                        case 66:  // key 'b' blanks screen (to black) or returns to the document
                        case 190: // and so does the key '.' (dot)
                            if ( presentationMode ) {
                                blankOut('#000');
                            }
                            break;
                        case 87:  // key 'w' blanks page (to white) or returns to the document
                        case 188: // and so does the key ',' (comma)
                            if ( presentationMode ) {
                                blankOut('#FFF');
                            }
                            break;
                        case 36: // key 'Home' goes to first page
                            self.showPage(1);
                            break;
                        case 35: // key 'End' goes to last page
                            self.showPage(pages.length);
                            break;
                    }
                }
            });
        }
    }

    init();
}
