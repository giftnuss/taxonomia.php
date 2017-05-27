

define(function () {
    var odfMimetypes      = [
        'application/vnd.oasis.opendocument.text',
        'application/vnd.oasis.opendocument.text-flat-xml',
        'application/vnd.oasis.opendocument.text-template',
        'application/vnd.oasis.opendocument.presentation',
        'application/vnd.oasis.opendocument.presentation-flat-xml',
        'application/vnd.oasis.opendocument.presentation-template',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.oasis.opendocument.spreadsheet-flat-xml',
        'application/vnd.oasis.opendocument.spreadsheet-template'];
    var odfFileExtensions = [
        'odt',
        'fodt',
        'ott',
        'odp',
        'fodp',
        'otp',
        'ods',
        'fods',
        'ots'];
    var pluginPath = "odf/ODFViewerPlugin";

    return {
        pluginName: "odf",
        supportsMimetype:      function ( mimetype ) {
            return (odfMimetypes.indexOf(mimetype) !== -1);
        },
        supportsFileExtension: function ( extension ) {
            return (odfFileExtensions.indexOf(extension) !== -1);
        },
        path: pluginPath,
        getClass:              function () {
            return require(pluginPath);
        }
    };
}),
