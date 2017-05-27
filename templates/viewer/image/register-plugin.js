

define(function () {
    var imageMimetypes      = [
        'image/jpeg',
        'image/pjpeg',
        'image/gif',
        'image/png',
        'image/bmp'];
    var imageFileExtensions = [
        'png',
        'jpg',
        'jpeg',
        'gif',
        'bmp'];
    var pluginPath = "image/ImageViewerPlugin";

    return {
        pluginName: "image",
        supportsMimetype:      function ( mimetype ) {
            return (imageMimetypes.indexOf(mimetype) !== -1);
        },
        supportsFileExtension: function ( extension ) {
            return (imageFileExtensions.indexOf(extension) !== -1);
        },
        path: pluginPath,
        getClass:              function () {
            return require(pluginPath);
        }
    };
}()),
