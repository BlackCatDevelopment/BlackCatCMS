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
	$.fn.fc_set_tab_list = function (options)
	{
		var defaults =
		{
			toggle_speed:	300,
			fc_list_forms:	$('.fc_list_forms'),
			fc_list_add:	$('#fc_list_add')
		};
		var options = $.extend(defaults, options);
		return this.each(function ()
		{
			var element			= $(this), // #fc_list_overview li
				items_ul		= element.closest('ul'),
				all_items		= items_ul.children('li'),
				form_id			= 'fc_list_' + element.children('input').val(),
				element_item	= $('#' + form_id),
				install_new		= $('#fc_install_new');

			options.fc_list_forms.not(':first').slideUp(0);
			if ( options.fc_list_add.size() === 0 )
			{
				all_items.filter(':first').addClass('fc_active');
			}
			else
			{
				options.fc_list_add.unbind().click( function()
				{
					all_items.removeClass('fc_active');
					options.fc_list_forms.not(install_new).slideUp(0);
					install_new.slideDown(0);
					install_new.find('ul.fc_groups_tabs a:first').click();
					install_new.find('input[type=text]:first').focus();
                    jQuery('#addon_details').html('');
				});
			}

			element.not('.fc_type_heading').click( function()
			{
				all_items.removeClass( 'fc_active' );
				element.addClass( 'fc_active' );
				// Hide all list-items and show only the clicked
				options.fc_list_forms.not('#' + form_id ).slideUp(0);
				element_item.slideDown(0);
				element_item.find('ul.fc_groups_tabs a:first').click();
                jQuery('#fc_add_new_module').hide();
			});

		});
	};
})(jQuery);
