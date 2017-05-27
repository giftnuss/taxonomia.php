
define(['js/underscore'],
    function () {
    var properties = {
        blankoutColor:      '#000',
        kScrollbarPadding:  40,
        minScale:           0.25,
        maxScale:           4.0,
        kDefaultScaleDelta: 1.1,
        presentationMode:   false,
        initialized:        false,
        blankedElement:     document.getElementById('blanked'),
        viewerElement:      document.getElementById('viewer'),
        canvasContainer:    document.getElementById('canvasContainer'),
        overlayNavigator:   document.getElementById('overlayNavigator'),
        titlebar:           document.getElementById('titlebar'),
        toolbar:            document.getElementById('toolbarContainer'),
        pageSwitcher:       document.getElementById('toolbarLeft'),
        zoomWidget:         document.getElementById('toolbarMiddleContainer'),
        scaleSelector:      document.getElementById('scaleSelect'),
        dialogOverlay:      document.getElementById('dialogOverlay'),
        toolbarRight:       document.getElementById('toolbarRight'),
        fxFadeDuration:     5000
    }, documentUrl,
       currentPage,
       scaleChangeTimer,
       touchTimer,
       toolbarTouchTimer,
       viewerPlugin,
       validZoomStrings = [
           "auto",
           "page-actual",
           "page-width",
           "page-height",
           "page-fit"
        ],
        isFullScreen = document.fullscreenElement,
        isFullscreenAvailable = document.fullscreenEnabled;

// ---------------------------------------------------------------------
// PRIVATE - Deal with the user interface.
// ---------------------------------------------------------------------

    function selectScaleOption( value ) {
        var zoomSelect = properties.scaleSelector,
            predefinedValueFound = false;
        if(!_.isNull(zoomSelect)) {
            // Retrieve the options from the zoom level <select> element
            var options = scaleSelector.options,
                option,
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
        }
        return predefinedValueFound;
    }

// ---------------------------------------------------------------------
// PRIVATE - Handle constructor arguments
//----------------------------------------------------------------------

    function readZoomParameter( zoom ) {
        if(!_.isUndefined(zoom)) {
            if ( validZoomStrings.indexOf(zoom) !== -1 ) {
                return zoom;
            }
            var number = parseFloat(zoom);
            if ( number && properties.minScale <= number &&
                    number <= properties.maxScale ) {
                return zoom;
            }
            console.log("Invalid zoom value given: '%s'.",zoom);
        }
        return 'auto';
    }

    var Viewer = function (props) {
        var zoom = props.zoom;
        delete props.zoom;

        for (prop in props) {
            properties[prop] = props[prop];
        }

    };
// ---------------------------------------------------------------------
// PUBLIC - Accessors
// ---------------------------------------------------------------------
    Viewer.setViewerPlugin = function (plugin) {
        viewerPlugin = plugin;
    };
// ---------------------------------------------------------------------
// Public - Load document
// ---------------------------------------------------------------------
    Viewer.renderDocument = function (url) {
        documentUrl = url;
        viewerPlugin.initialize(properties.canvasContainer, url);

    }
// ---------------------------------------------------------------------
// PUBLIC - Control blank out, fullscreen and presentation mode
// ---------------------------------------------------------------------
    Viewer.isBlankedOut = function () {
        return (properties.blankedElement.style.display === 'block');
    };

    Viewer.blankOut = function () {
        properties.blankedElement.style.display = 'block';
        properties.blankedElement.style.backgroundColor = properties.blankoutColor;
        this.hideToolbars();
    };

    Viewer.leaveBlankOut = function () {
        properties.blankedElement.style.display = 'none';
        this.toggleToolbars();
    };

    Viewer.cancelPresentationMode = function () {
        if ( properties.presentationMode && !isFullScreen ) {
            this.togglePresentationMode();
        }
    }

    Viewer.toggleFullScreen = function () {
        var elem = properties.viewerElement;
        if(isFullscreenAvailable) {
            if ( !isFullScreen ) {
                if ( elem.requestFullscreen ) {
                    elem.requestFullscreen();
                }
            } else {
                if ( document.exitFullscreen ) {
                    document.exitFullscreen();
                }
            }
        }
    };

    function handleFullScreenChange() {
        isFullScreen = !isFullScreen;
        Viewer.cancelPresentationMode();
    }

    document.addEventListener('fullscreenchange', handleFullScreenChange);
// ---------------------------------------------------------------------
// PUBLIC - Page(s)
// ---------------------------------------------------------------------
    /**
     * Shows the 'n'th page. If n is larger than the page count,
     * shows the last page. If n is less than 1, shows the first page.
     * @return {undefined}
     */
    Viewer.showPage = function ( n ) {
        if ( n <= 0 ) {
            n = 1;
        } else if ( n > this.pageCount() ) {
            n = this.pageCount();
        }

        viewerPlugin.showPage(n);

        currentPage = n;
        document.getElementById('pageNumber').value = currentPage;
    };

    /**
     * Shows the next page. If there is no subsequent page, does nothing.
     * @return {undefined}
     */
    Viewer.showNextPage = function () {
        this.showPage(currentPage + 1);
    };

    /**
     * Shows the previous page. If there is no previous page, does nothing.
     * @return {undefined}
     */
    Viewer.showPreviousPage = function () {
        this.showPage(currentPage - 1);
    };

    /**
     * Get the number of pages from plugin
     */
    Viewer.pageCount = function () {
        return viewerPlugin.pageCount();
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

            }
        }
        init();

        return Viewer;
    });
//});
