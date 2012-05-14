/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @file GeSHi Syntax Highlighter
 */

/* Register a plugin named "pixieGeSHi". */
CKEDITOR.plugins.add( 'GeSHi',
{

        requires: [ 'iframedialog' ],
        lang : [ 'en' ],

        init : function( editor )
        {

                var pluginName = 'GeSHi';

                /* Register the dialog. */
                CKEDITOR.dialog.addIframe('GeSHi', 'GeSHi Parser',this.path + 'dialogs/dialog.php',700,500,function(){ /* oniframeload */ })

                var command = editor.addCommand( 'GeSHi', new CKEDITOR.dialogCommand( 'GeSHi' ) );
                command.modes = { wysiwyg:1, source:1 };
                command.canUndo = false;

                /* Set the language and the command */
                editor.ui.addButton( 'GeSHi',
                        {
                                label : editor.lang.langGeSHi.label,
                                command : pluginName,
                                icon: this.path + 'GeSHi.gif'
                        });

        },

})
