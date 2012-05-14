/**
 * @category        modules
 * @package         wysiwyg
 * @author          WebsiteBaker Project, Michael Tenschert
 * @copyright       2010, Michael Tenschert
 * @link            http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/lgpl.html
 */

// Register the related commands.
CKEDITOR.plugins.add('dropleps',
{
    lang : ['en','de','nl'],
    init: function(editor)
    {
        editor.addCommand('droplepsDlg', new CKEDITOR.dialogCommand('droplepsDlg'));
        editor.ui.addButton('dropleps',
            {
                label: editor.lang.dropleps.btn,
                command: 'droplepsDlg',
                icon: this.path + 'images/dropleps.gif'
            });
        CKEDITOR.dialog.add('droplepsDlg', this.path + 'dialogs/dropleps.php');
    }
});