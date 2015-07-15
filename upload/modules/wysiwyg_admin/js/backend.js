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
        $('a.tooltip').each(function() {
            $(this).qtip({
                content: {
                    text: $(this).next('div.tooltiptext')
                },
                hide: {
                    fixed: true,
                    delay: 300
                }
            });
        });
        $('div.legend').each(function() {
            // find block; ends with next legend or </fieldset>
            $(this).nextUntil('div.legend').andSelf().wrapAll("<div id='toggle_"+$(this).prop('id')+"'></div>");
            // hide options if the plugin is not activated
            if( ! $('input#plugins_'+$(this).prop('id').replace(/_plugin$/,'')).is(':checked') ) {
                $(this).parent().hide();
            }
        });
        // add onclick event to the plugin checkboxes
        $('input[name="plugins[]"]').unbind('click').on('click', function() {
            if($(this).is(':checked')) {
                $('div#toggle_'+$(this).prop('id').replace(/plugins_/,'')).show('slow');
            } else {
                $('div#toggle_'+$(this).prop('id').replace(/plugins_/,'')).hide('slow');
            }
        });
    });
}
