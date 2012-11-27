/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author		  LEPTON Project
 * @copyright	   2012, LEPTON Project
 * @link			http://www.LEPTON-cms.org
 * @license		 http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */

(function ($) {
	$.fn.fc_toggle_element = function (options)
	{
		var defaults =
		{
			show_on_start:	false,
			toggle_speed:	300
		};
		var options = $.extend(defaults, options);
		return this.each(function ()
		{
			// Storing $(this) in a variable
			var current = $(this);
			var action = 'show';

			current.addClass('toggle_element');

			if ( current.is('select') )
			{
				//var according_div = $('#' + current.attr('rel') );
				var current_option	= current.children('option:selected'),
					div_id			= match_class_prefix( 'show___', current_option ),
					action			= 'show';
				if( typeof div_id == 'undefined' || div_id.length == 0 )
				{
					div_id	= match_class_prefix( 'hide___', current_option );
					action	= 'hide';
				}
				if( typeof div_id != 'undefined' )
				{
					according_div = $('#' + div_id);
				}
				// if no value for show_on_start is given or if it is set to false hide the according div
				if ( options.show_on_start == false )
				{
					according_div.slideUp(0).addClass('fc_inactive_element hidden').removeClass('fc_active_element');
				}
				else
				{
					according_div.slideDown(0).addClass('fc_active_element').removeClass('fc_inactive_element hidden');
				}
				// Bind element with change event to toggle/hide according to the rel-tag
				current.change( function()
				{
					//var according_div = $('#' + current.attr('rel') );
					var current_option	= current.children('option:selected'),
						div_id			= match_class_prefix( 'show___', current_option ),
						action			= 'show';
					if( typeof div_id == 'undefined' || div_id.length == 0 )
					{
						div_id	= match_class_prefix( 'hide___', current_option );
						action	= 'hide';
					}
					if( typeof div_id != 'undefined' )
					{
						according_div = $('#' + div_id);
					}
	
					if ( action == 'show' )
					{
						according_div.removeClass('hidden').slideUp(0).slideDown(options.toggle_speed, function()
						{
							according_div.addClass('fc_active_element').removeClass('fc_inactive_element');
						});
					}
					else
					{
						according_div.slideUp(options.toggle_speed, function()
						{
							according_div.addClass('fc_inactive_element hidden').removeClass('fc_active_element');
						});
					}
				}).change();
			}
			else if ( current.is('input:checkbox') || current.is('input:radio') )
			{

				var according_div = $('#' + current.attr('rel') );
			
				// if multiple elements control an ID (the class-tag must provide action___id)
				var div_id		= match_class_prefix('show___',current);
				var action		= 'show';
				if( typeof div_id == 'undefined' || div_id.length == 0 )
				{
					div_id = match_class_prefix('hide___',current);
					action = 'hide';
				}
			
				if( typeof div_id != 'undefined' )
				{
					according_div = $('#'+div_id);
				}
			
				if ( typeof according_div != 'undefined' && according_div.length > 0 )
				{
					if ( action == 'show' && current.attr( 'checked' ) )
					{
						options.show_on_start = true;
					}
					else if ( action == 'hide' && current.attr( 'checked' ) )
					{
						options.show_on_start = false;
					}
				}
			
				// if no value for show_on_start is given or if it is set to false hide the according div
				if ( options.show_on_start == false )
				{
					according_div.slideUp(0).addClass('fc_inactive_element hidden').removeClass('fc_active_element');
				}
				else
				{
					according_div.slideDown(0).addClass('fc_active_element').removeClass('fc_inactive_element');
				}
				
				// bind the change event - the "hidden" class is needed for elements placed in a dialog as calling .dialog() will show every element inside the dialog
				current.click( function()
				{
					if ( current.attr( 'checked' ) && action == 'show' )
					{
						according_div.removeClass('hidden').slideUp(0).slideDown(options.toggle_speed, function()
						{
							according_div.addClass('fc_active_element').removeClass('fc_inactive_element');
						});
					}
					else
					{
						according_div.slideUp(options.toggle_speed, function()
						{
							according_div.addClass('fc_inactive_element hidden').removeClass('fc_active_element');
						});
					}
				});
			}
		})
	}
})(jQuery);
