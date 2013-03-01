/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
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