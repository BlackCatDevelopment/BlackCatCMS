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

			//current.addClass('toggle_element');
			if ( current.is('select') )
			{
				//var according_div = $('#' + current.prop('rel') );
				var current_option	= current.children('option:selected'),
					div_id			= match_class_prefix( 'show___', current_option ),
					action			= 'show';
				if( typeof div_id == 'undefined' || div_id.length === 0 )
				{
					div_id	= match_class_prefix( 'hide___', current_option );
					action	= 'hide';
				}
				if( typeof div_id != 'undefined' )
				{
					according_div = $('#' + div_id);
				}
				// if no value for show_on_start is given or if it is set to false hide the according div
				if ( options.show_on_start === false )
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
					//var according_div = $('#' + current.prop('rel') );
					var current_option	= current.children('option:selected'),
						div_id			= match_class_prefix( 'show___', current_option ),
						action			= 'show';
					if( typeof div_id == 'undefined' || div_id.length === 0 )
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

				var according_div = $('#' + current.prop('rel') );
				// if multiple elements control an ID (the class-tag must provide action___id)
				var div_id		= match_class_prefix('show___',current);
				var action		= 'show';
				if( typeof div_id == 'undefined' || div_id.length === 0 )
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
					console.log(div_id + ': ' + action + ' - ' +  current.prop( 'checked' ));
					if ( action == 'show' && current.prop( 'checked' ) )
					{
						options.show_on_start = true;
					}
					else if ( action == 'hide' && current.prop( 'checked' ) )
					{
						options.show_on_start = false;
					}
					else if ( action == 'show' && !current.prop( 'checked' ) )
					{
						options.show_on_start = false;
					}
				}

				//console.log(according_div + current.prop('rel') + ': ' + show_on_start);
				// if no value for show_on_start is given or if it is set to false hide the according div
				if ( options.show_on_start === false )
				{
				console.log(div_id + ' ' + action + ' - ' + options.show_on_start);
					according_div.addClass('fc_inactive_element hidden').slideUp(0).removeClass('fc_active_element');
				}
				else
				{
					according_div.slideDown(0).addClass('fc_active_element').removeClass('hidden fc_inactive_element');
				}
				
				// bind the change event - the "hidden" class is needed for elements placed in a dialog as calling .dialog() will show every element inside the dialog
				current.click( function()
				{
					if ( current.prop( 'checked' ) && action == 'show' )
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
		});
	};
})(jQuery);
