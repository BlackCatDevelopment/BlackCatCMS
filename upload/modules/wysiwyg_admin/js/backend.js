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
        $('#editor_skin').bind('change',function() {
            var src = $('#wysiwyg_admin_skin_preview').find('img').attr('src');
            src = src.replace( /(.*)\/.*(\.png$)/i, '$1/'+$('#editor_skin').val()+'$2' );
            $('#wysiwyg_admin_skin_preview').find('img').attr('src',src).attr('title',$('#editor_skin').val()).attr('alt',$('#editor_skin').val());
        });
    });
}
