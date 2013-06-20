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

jQuery(document).ready(function()
{
	$('#fc_preferences_submit').click( function()
	{
		if ( $('.popup').size() === 0 )
		{
			$('<div class="popup" />').appendTo('body');
		}
		$('.popup').html('<div class="c_16">Confirm with current password</div>');
		var button		= CAT_TEXT["BACK"];
		if ( !$('#fc_current_password').val() )
		{
			$('.popup').dialog(
			{
				title: 'Confirm with current password',
				modal: true,
				buttons: [
				{
					text: button,
					click: function()
					{
						$(this).dialog("close");
						$('.popup').remove();
					}
				}
				]
			});
			return false;
		}
	});
});