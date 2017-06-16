
(function () {
    var type = '<?php echo $type; ?>',
      id = '<?php echo $document; ?>';

    requirejs.config({
        paths: {
            js: '/core/js'
        }
    });

    require(
       [
         'common/document-viewer',
         'common/setup',
         type + '/register-plugin'
       ],function (doc, setup, pluginData) {
         doc.registerPlugin(pluginData);
         doc.usePlugin(type);
         if(type === 'markitup') {
             doc.setDocumentUrl('/view/text/' + id);
         }
         else {
             doc.setDocumentUrl('/document/' + id);
         }
         doc.render(setup.viewer);
    });
})();
