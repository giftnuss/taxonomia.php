
define(function () {
    var pluginPath = "markitup/markitupPlugin";
    return {
        pluginName: "markitup",
        supportsMimetype: function ( mimetype ) {
            return (mimetype === 'plain/text');
        },
        supportsFileExtension: function ( extension ) {
            return (extension === 'txt' || extension === 'md');
        },
        path: pluginPath,
        getClass: function () { return require(pluginPath) }
    };
});
