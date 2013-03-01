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
				});
			}

			element.click( function()
			{
				all_items.removeClass( 'fc_active' );
				element.addClass( 'fc_active' );
				// Hide all list-items and show only the clicked
				options.fc_list_forms.not('#' + form_id ).slideUp(0);
				element_item.slideDown(0);
				element_item.find('ul.fc_groups_tabs a:first').click();
			});

		});
	};
})(jQuery);
