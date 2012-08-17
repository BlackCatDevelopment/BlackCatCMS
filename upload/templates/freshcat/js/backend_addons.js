/**
 * This file is part of LEPTON Core, released under the GNU GPL
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
 *
 */

jQuery(document).ready(function(){
	$('#fc_list_overview li').fc_set_tab_list();
	$('#fc_mark_all').click( function()
	{
		var current		= $(this);
		var input_div	= $('#' + current.attr('rel'));
		current.toggleClass( 'fc_marked' );
		if ( current.hasClass( 'fc_marked' ) )
		{
			input_div.children( 'input' ).attr( 'checked', true).change();
			current.children( '.fc_mark' ).addClass('hidden');
			current.children( '.fc_unmark' ).removeClass('hidden');
		}
		else
		{
			input_div.children( 'input' ).attr( 'checked', false).change();
			current.children( '.fc_unmark' ).addClass('hidden');
			current.children( '.fc_mark' ).removeClass('hidden');
		}
	});
});