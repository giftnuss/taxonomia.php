

define(['js/underscore'],
function () {

    function estimateTypeByHeaderContentType( documentUrl, params, cb ) {
        var xhr                = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            var mimetype, matchingPluginData;
            if ( xhr.readyState === 4 ) {
                if ( (xhr.status >= 200 && xhr.status < 300) || xhr.status === 0 ) {
                    mimetype = xhr.getResponseHeader('content-type');
                    if (mimetype == null && params.mimetype) {
                        mimetype = params.mimetype;
                    }

                    if ( mimetype ) {
                        pluginRegistry.some(function ( pluginData ) {
                            if ( pluginData.supportsMimetype(mimetype) ) {
                                matchingPluginData = pluginData;
                                console.log('Found plugin by mimetype and xhr head: ' + mimetype);
                                // store the mimetype globally
                                window.mimetype = mimetype;
                                return true;
                            }
                            return false;
                        });
                    }
                }
                if ( !matchingPluginData ) {
                    matchingPluginData = unknownFileType;
                }
                cb(matchingPluginData);
            }
        };
        xhr.open("HEAD", documentUrl, true);
        xhr.send();
    }

    function estimateTypeByFileExtension( extension ) {
        var matchingPluginData = doEstimateTypeByFileExtension(extension)

        if ( matchingPluginData ) {
            console.log('Found plugin by parameter type: ' + extension);

            // this is needed for the Multimedia Plugin
            window.mimetype = getMimeByExtension(extension);
        }

        return matchingPluginData;
    }

    function parseSearchParameters( location ) {
        var parameters = {},
            search     = location.search || "?";

        search.substr(1).split('&').forEach(function ( q ) {
            // skip empty strings
            if ( !q ) {
                return;
            }
            // if there is no '=', have it handled as if given key was set to undefined
            var s                                = q.split('=', 2);
            parameters[decodeURIComponent(s[0])] = decodeURIComponent(s[1]);
        });

        return parameters;
    }

    function getMimeByExtension( ext ) {
        var extToMimes = {
            'aac':  'audio/aac',
            'mp4':  'video/mp4',
            'm4a':  'audio/mp4',
            'mp3':  'audio/mpeg',
            'mpg':  'video/mpeg',
            'mpeg': 'video/mpeg',
            'ogg':  'video/ogg',
            'wav':  'audio/wav',
            'webm': 'video/webm',
            'm4v':  'video/mp4',
            'ogv':  'video/ogg',
            'oga':  'audio/ogg',
            'mp1':  'audio/mpeg',
            'mp2':  'audio/mpeg'
        };

        if ( extToMimes.hasOwnProperty(ext) ) {
            return extToMimes[ext];
        }
        return false;
    }

    function getQueryParams(qs) {
        qs = qs.split('+').join(' ');

        var params = {},
            tokens,
            re = /[?&]?([^=]+)=([^&]*)/g;

        while (tokens = re.exec(qs)) {
            params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
        }

        return params;
    }

    var window_onload = function () {
        var viewer,
            documentUrl = document.location.hash.substring(1),
            parameters  = parseSearchParameters(document.location),
            params = getQueryParams(document.location.search),
            Plugin;

        documentUrl = "/document/2327";
        parameters.type = "pdf";

        if ( documentUrl ) {
            // try to guess the title as filename from the location, if not set by parameter
            if ( !parameters.title ) {
                parameters.title = documentUrl.replace(/^.*[\\\/]/, '');
            }

            parameters.documentUrl = documentUrl;

            // trust the server most
            estimateTypeByHeaderContentType(documentUrl, params, function ( pluginData ) {
                if ( !pluginData ) {
                    if ( parameters.type ) {
                        pluginData = estimateTypeByFileExtension(parameters.type);
                    } else {
                        // last ressort: try to guess from path
                        pluginData = estimateTypeByFileExtensionFromPath(documentUrl);
                    }
                }

                if ( pluginData ) {
                    if ( String(typeof loadPlugin) !== "undefined" ) {
                        loadPlugin(pluginData.path, function () {
                            Plugin = pluginData.getClass();
                            viewer = new Viewer(new Plugin(), parameters);
                        });
                    } else {
                        Plugin = pluginData.getClass();
                        viewer = new Viewer(new Plugin(), parameters);
                    }
                } else {
                    viewer = new Viewer();
                }
            });
        } else {
            viewer = new Viewer();
        }
    };

    function getPluginByName(registry, name) {
        var matchingPluginData;

        registry.some(function (pluginData) {
            if( pluginData.pluginName === name ) {
                matchingPluginData = pluginData;
                return true;
            }
            return false;
        });
        return matchingPluginData;
    }

    function doEstimateTypeByFileExtension( registry, extension ) {
        var matchingPluginData;

        registry.some(function ( pluginData ) {
            if ( pluginData.supportsFileExtension(extension) ) {
                matchingPluginData = pluginData;
                return true;
            }
            return false;
        });

        return matchingPluginData;
    }

    function estimateTypeByFileExtensionFromPath( registry, documentUrl ) {
        // See to get any path from the url and grep what could be a file extension
        var documentPath       = documentUrl.split('?')[0],
            extension          = documentPath.split('.').pop(),
            matchingPluginData = doEstimateTypeByFileExtension(registry, extension)

        return matchingPluginData;
    }

    return function () {
        var documentUrl = null,
        usePlugin = null,
        pluginRegistry = [ ],
        unknownFileType = {
            pluginName: "unknown",
            supportsMimetype: function () {
                return true;
            },
            supportsFileExtension: function () {
                return true;
            },
            path: "common/UnknownFilePlugin",
            getClass:              function () {
                return require("common/UnknownFilePlugin");
            }
        };

        return {
            registerPlugin: function (data) {
                return pluginRegistry.unshift(data);
            },
            setDocumentUrl: function (url) {
                documentUrl = url;
            },
            usePlugin: function (name) {
                usePlugin = getPluginByName(pluginRegistry, name);
            },
            render: function (viewer) {
                if(!_.isObject(usePlugin)) {
                    throw new ReferenceError("Can not start rendering. No plugin data found.");
                }
                if(!_.isString(documentUrl)) {
                    throw new ReferenceError("Document url is not specified.");
                }
                require([usePlugin.path], function (plugin) {
                    viewer.setViewerPlugin(plugin);
                    viewer.renderDocument(documentUrl);
                });
            }
        };
    }();
});
