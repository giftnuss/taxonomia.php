
define([
    "markitup/markitup.jquery"
], function () {
        var Plugin = function () {

        };
        Plugin.initialize = function (viewContainer, location) {
            var textarea = $('textarea',viewContainer);
            var settings =  {
                preview: false,
                tabs: '    ',
               // previewRefreshOn: [ 'markitup.insertion', 'keyup' ],
               // shortcuts: {
               //     'Ctrl Shift R': function (e) {
               //         this.refreshPreview();
               //         e.preventDefault();
               //     }
               // },
               // beforePreviewRefresh: function (content, callback) {
               //     callback(converter.makeHtml(content));
               // },
                toolbar: [
                    {
                        name: 'Save',
                        icon: 'file',
                        shortcut: 'Ctrl S',
                        click: function () {
                            var saveit = location.replace("view/",'save/');
                            $.post({
                                url: saveit,
                                data: {text: this.getContent()},
                                error: function (jqXHR) {
                                    alert('eee');
                                },
                                success: function (data,jqXHR) {

                                }
                            });
                        }
                    },
                    {
                        separator: true
                    },
                    {
                        name: "Clean",
                        icon: 'trash-o',
                        shortcut: 'Ctrl R',
                        dialog: {
                             header: 'Delete',
                             url: 'dialogs/delete.html',
                             afterOpen: function (settings) {
                                 var sel = this.getSelection();
                                 settings['search'] = sel.text;
                                 $('.remove-text').val(sel.text);
                             },
                             // callback is possible because an customized version is used
                             callback: function (vars) {
                                  var remove = vars['remove'];
                                  if(remove.length >0) {
                                      var content = this.getContent();
                                      // how to quotemeta remove, maybe it is a feature, not a bug :)
                                      content =content.replace(new RegExp(remove,"mg"),'');
                                      this.setContent(content);
                                  }
                             }
                        }
                    },
                    {
                        separator: true
                    },
                    {   name: 'Link',
                        icon: 'link',
                        shortcut: 'Ctrl Shift L',
                        content: '[{S:}{VAR placeholder}{:S}]({VAR link}{IF title:} "{VAR title}"{:IF})',
                        dialog: {
                            header: 'Links',
                            url: 'dialogs/link.html'
                        },
                    },
                    {   name: 'Picture',
                        icon: 'picture',
                        shortcut: 'Ctrl Shift P',
                        content: '![{VAR alt}]({VAR url})',
                        dialog: {
                            header: 'Picture',
                            url: 'dialogs/picture.html'
                        }
                    },
                    {
                        separator: true
                    },
                    {   name: 'Headings',
                        icon: 'header',
                        dropdown: [
                            {   name: 'Heading level 1',
                                shortcut: 'Ctrl Shift 1',
                                before: '# ',
                                after: '\n'
                            },
                            {   name: 'Heading level 2',
                                shortcut: 'Ctrl Shift 2',
                                before: '## ',
                                after: '\n'
                            },
                            {   name: 'Heading level 3',
                                shortcut: 'Ctrl Shift 3',
                                before: '### ',
                                after: '\n'
                            },
                            {   name: 'Heading level 4',
                                shortcut: 'Ctrl Shift 4',
                                before: '#### ',
                                after: '\n'
                            }
                        ]
                    },
                    {   name: 'Bold',
                        icon: 'bold',
                        shortcut: 'Ctrl Shift B',
                        before: '**',
                        after: '**'
                    },
                    {   name: 'Italic',
                        icon: 'italic',
                        shortcut: 'Ctrl Shift I',
                        before: '*',
                        after: '*'
                    },
                    {
                        separator: true
                    },
                    {   name: 'Unordered List',
                        icon: 'list-ul',
                        before: '* ',
                        multiline: true
                    },
                    {   name: 'Ordered List',
                        icon: 'list-ol',
                        before: '{#}. ',
                        multiline: true
                    },
                    {
                        separator: true
                    },
                    {   name: 'Indent',
                        icon: 'indent',
                        click: function () {
                            this.indent();
                        }
                    },
                    {   name: 'Outdent',
                        icon: 'outdent',
                        click: function () {
                            this.outdent();
                        }
                    },
                ]
            };

            $.get(location,function (data,status,jqXHR) {
                textarea.append(data);
                textarea.markitup(settings);

                $(window).on('resize', function(){
                    var minus = $('.markitup-toolbar').height();
                    var height = $(window).height() - minus - 50;
                    $('textarea',viewContainer).height(height);
                }).trigger('resize');
            });

        }
        return Plugin;
    });
