
define(function () {
    var pluginPath = "pdf/PDFViewerPlugin";
    return {
        pluginName: "pdf",
        supportsMimetype: function ( mimetype ) {
            return (mimetype === 'application/pdf');
        },
        supportsFileExtension: function ( extension ) {
            return (extension === 'pdf');
        },
        path: pluginPath,
        getClass: function () { return require(pluginPath) }
    };
});
