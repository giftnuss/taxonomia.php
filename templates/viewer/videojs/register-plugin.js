
define(function () {
    var multimediaMimetypes      = [
        'video/mp4',
        'video/mpeg',
        'video/ogg',
        'video/webm',
        'video/x-m4v',

        'audio/aac',
        'audio/mp4',
        'audio/mpeg',
        'audio/ogg',
        'audio/wav',
        'audio/x-wav',
        'audio/webm'];
    var multimediaFileExtensions = [
        'aac',
        'mp4',
        'm4a',
        'mp3',
        'mpg',
        'mpeg',
        'ogg',
        'wav',
        'webm',
        'm4v',
        'ogv',
        'oga',
        'mp1',
        'mp2'];
    var pluginPath = "videojs/MultimediaViewerPlugin";

    return {
        pluginName: "videojs",
        supportsMimetype:      function ( mimetype ) {
            return (multimediaMimetypes.indexOf(mimetype) !== -1);
        },
        supportsFileExtension: function ( extension ) {
            return (multimediaFileExtensions.indexOf(extension) !== -1);
        },
        path: pluginPath,
        getClass:              function () {
            return require(pluginPath);
        }
    };
})
