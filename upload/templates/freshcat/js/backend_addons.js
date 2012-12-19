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
 */

jQuery(document).ready(function(){
	$('#fc_list_overview li').fc_set_tab_list();
	$('#fc_mark_all').click( function(e)
	{
		e.preventDefault();
		var current		= $(this),
			input_div	= $('#fc_perm_groups');
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
	
	$('#fc_list_overview').children('li').not('.fc_type_modules').addClass('fc_no_search').slideUp(0);
	$('#fc_list_search_input').blur();
	$('#fc_lists_overview button').not('#fc_list_add').click( function()
	{
		var current_button	= $(this),
			modules			= $('#fc_list_overview').children('li.fc_type_modules'),
			templates		= $('#fc_list_overview').children('li.fc_type_templates'),
			languages		= $('#fc_list_overview').children('li.fc_type_languages');

		current_button.toggleClass('fc_active');
		if ( current_button.hasClass('icon-puzzle') )
		{
			var slide	= modules;
		}
		else if ( current_button.hasClass('icon-color-palette') )
		{
			var slide	= templates;
		}
		else if ( current_button.hasClass('icon-comments') )
		{
			var slide	= languages;
		}

		if ( current_button.hasClass('fc_active') )
		{
			slide.removeClass('fc_no_search').stop().slideDown(300);
		}
		else
		{
			slide.addClass('fc_no_search').stop().slideUp(300);
		}
	});
});