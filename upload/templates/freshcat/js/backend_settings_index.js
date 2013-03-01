/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 * 
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         freshcat
  *
 */

function send_testmail(URL) {
    if ( typeof jQuery != 'undefined' ) {
        jQuery.ajax({
            type: 'POST',
            url:  URL,
            data: {'_cat_ajax': 1},
            success:	function( data, textStatus, jqXHR  ) {
                jQuery('#testmail_result').html(data).show();
            }
        });
    }
}

function create_guid(URL) {
    if ( typeof jQuery != 'undefined' ) {
        jQuery.ajax({
            type: 'GET',
            url:  URL,
            success:	function( data, textStatus, jqXHR  ) {
                jQuery('#guid').html(data);
                $('#fc_createguid').hide();
            },
            error: function(data, textStatus, jqXHR) { alert(textStatus); }
        });
    }
}

jQuery(document).ready(function(){
	$('#fc_list_overview li').fc_set_tab_list();
	$('select[name=default_theme]').change( function()
	{
		$(this).closest('form').removeClass('ajaxForm').unbind();
	});
    $('#fc_createguid').click(function()
    {
        create_guid(CAT_ADMIN_URL+'/settings/ajax_guid.php');
    });
});