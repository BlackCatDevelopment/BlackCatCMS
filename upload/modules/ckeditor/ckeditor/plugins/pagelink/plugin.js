/**
 * @category        modules
 * @package         wysiwyg
 * @author          WebsiteBaker Project, Michael Tenschert
 * @copyright       2010, Michael Tenschert
 * @link            http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/lgpl.html
 */

// Register the related commands.
CKEDITOR.plugins.add('pagelink',
{
    lang : ['en','de','nl','ru'],
    init: function(editor)
    {
        var pluginName = 'pagelink';
        editor.addCommand('pagelinkDlg', new CKEDITOR.dialogCommand('pagelinkDlg'));
        editor.ui.addButton('pagelink',
            {
                label: editor.lang.pagelink.btn,
                command: 'pagelinkDlg',
                icon: this.path + 'images/pagelink.gif'
            });
        CKEDITOR.dialog.add('pagelinkDlg', this.path + 'dialogs/pagelink.php');
    }
});