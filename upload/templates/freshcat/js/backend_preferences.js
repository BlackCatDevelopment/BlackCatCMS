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
 *   @copyright       2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         freshcat
 *
 */

jQuery(document).ready(function()
{
    var fields = new Array( 'fc_pref_display_name', 'fc_language', 'fc_timezone_string', 'fc_date_format', 'fc_time_format', 'fc_email' );
    for( i=0; i<=fields.length; i++ )
	{
        $('#'+fields[i]).change( function() {
            $('#fc_modifyUser_currentpw').show('slide');
        });
    }
    $('#fc_change_pw').click( function(e) {
        e.preventDefault();
        $('#fc_modifyUser_setnewpw').show('slide');
        $('#fc_modifyUser_currentpw').show('slide');
        $(this).hide();
    });
    $('.fc_modifyUser_reset').click( function() {
        $('#fc_modifyUser_setnewpw').hide('slide');
        $('#fc_modifyUser_currentpw').hide('slide');
        $('#fc_change_pw').show();
    });
	$('.fc_preferences_submit').click( function(e)
	{
        if ( !$('#fc_current_password').val() )
		{
            e.preventDefault();
		if ( $('.popup').size() === 0 )
		{
			$('<div class="popup" />').appendTo('body');
		}
    		$('.popup').html('<div class="c_16">'+cattranslate('Confirm with current password')+'</div>');
		var button		= cattranslate('Back');
		if ( !$('#fc_current_password').val() )
		{
			$('.popup').dialog(
			{
    				title: cattranslate('Confirm with current password'),
				modal: true,
				buttons: [
				{
					text: button,
					click: function()
					{
						$(this).dialog("close");
                            $('#fc_modifyUser_currentpw').show('slide');
						$('.popup').remove();
					}
				}
				]
			});
			return false;
		}
		}
        else
        {
            $('.fc_modifyUser_reset').click();
        }
	});
});