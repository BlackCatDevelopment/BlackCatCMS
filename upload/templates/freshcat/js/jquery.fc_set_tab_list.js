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
 * @version         $Id$
 *
 */

(function ($) {
	$.fn.fc_set_tab_list = function (options)
	{
		var defaults =
		{
			toggle_speed:	300
		};
		var options = $.extend(defaults, options);
		return this.each(function ()
		{
			var element			= $(this), // #fc_list_overview li
				all_items		= element.closest('ul'),
				form_id			= element.attr('rel'),
				element_item	= $('#' + form_id);

			// Initial hide of forms and descriptions
			element_item.find( '.fc_input_description' ).slideUp(0).addClass('hidden');
			$('.fc_list_forms').not(':first').slideUp(0);
			if ( $('#fc_list_add').size() == 0 )
			{
				all_items.children('li:first').addClass('fc_active');
			}
			else
			{
				$('#fc_list_add').unbind().click( function()
				{
					all_items.children('li').removeClass('fc_active');
					$('.fc_list_forms').not('#fc_install_new').slideUp(0);
					$('#fc_install_new').slideDown(0);
				});
			}

			element.click( function()
			{
				all_items.children('li').removeClass( 'fc_active' );
				element.addClass( 'fc_active' );
				// Hide all list-items and show only the clicked
				$('.fc_list_forms').not('#' + form_id ).slideUp(0);
				element_item.slideDown(0).find('.fc_input_description').removeClass('hidden').slideUp(0);
			});
			
			// Bind input-fields to show description on focus
			element_item.find('input').focus(function()
			{
				var input_description		= $(this).attr('id');
				element_item.find('.fc_input_description').not('.' + input_description).slideUp( options.toggle_speed );
				element_item.find('.' + input_description).removeClass('hidden').slideUp(0).slideDown( options.toggle_speed );
			});
			
			// Bind the delete button with ajax-send
			element_item.find('.fc_list_remove').click(function()
			{
				// find the current active list-item
				var current_clicked		= $(this),
					current_item		= current_clicked.closest('.fc_list_forms');
			
				if ( current_clicked.hasClass('fc_user_remove') )
				{
					// get the id and the name of list-item
					var user_id		= current_item.find('input[name=user_id]').val(),
						user_name	= current_item.find('#fc_name_'+user_id).val(),
						link		= ADMIN_URL + '/users/delete.php?user_id=' + user_id,
						title		= LEPTON_TEXT["MANAGE_USERS"],
						message		= LEPTON_TEXT["USERS_CONFIRM_DELETE"]+':<br/><strong>' + user_name + '</strong>';
				}
				else if (current_clicked.hasClass('fc_group_remove') )
				{
					// get the id and the name of list-item
					var user_id		= current_item.find('input[name=group_id]').val(),
						user_name	= current_item.find('input[name=name]').val(),
						link		= ADMIN_URL + '/groups/delete.php?group_id=' + user_id,
						title		= LEPTON_TEXT["MANAGE_GROUPS"],
						message		= LEPTON_TEXT["GROUPS_CONFIRM_DELETE"]+':<br/><strong>' + user_name + '</strong>';
				}
				else
				{
					return false;
				}
			
				// Define function afterSend
				var afterDelete = function()
				{
					var element				= $(this),
						removeForm			= $('#' + element.attr('rel')),
						count_list_items	= $('#fc_list_overview li').size();
			
					if ( count_list_items > 1 )
					{
						if( element.index() < count_list_items-1 )
						{
							element.next('li').addClass('fc_active').click();
						}
						else
						{
							element.prev('li').addClass('fc_active').click();
						}
						element.remove();
						removeForm.remove();
					}
					else
					{
						element.remove();
						removeForm.remove();
						$('#fc_list_add').click();
					}
				}
				dialog_confirm( '<div class="popup_header">'+title+'</div><div class="popup_content">'+message+'</div>', link, false, afterDelete, element);
			});
		});
	}
})(jQuery);
