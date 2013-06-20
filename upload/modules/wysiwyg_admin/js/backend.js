/**
 *
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         wysiwyg_admin
 *
 */

if ( typeof jQuery != 'undefined' ) {
    $(document).ready(function($) {
        $('select#skin').bind('change',function() {
            var src = $('#wysiwyg_admin_skin_preview').find('img').attr('src');
            src = src.replace( /(.*)\/.*(\.png$)/i, '$1/'+$('select#skin').val()+'$2' );
            $('#wysiwyg_admin_skin_preview').find('img').attr('src',src).attr('title',$('select#skin').val()).attr('alt',$('select#skin').val());
        });
    });
}
