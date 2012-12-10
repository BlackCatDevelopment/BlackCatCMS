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
				form_id			= element.attr('rel'),
				element_item	= $('#' + form_id),
				install_new		= $('#fc_install_new');

			// Initial hide of forms and descriptions
			//element_item.find( '.fc_input_description' ).slideUp(0).addClass('hidden');

			options.fc_list_forms.not(':first').slideUp(0);
			if ( options.fc_list_add.size() == 0 )
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
			

			
			// Bind the delete button with ajax-send
			/*element_item.find('.fc_list_remove').click( function(e)
			{
				e.preventDefault();
				// find the current active list-item
				var current_clicked		= $(this),
					current_item		= current_clicked.closest('.fc_list_forms');
			
				if ( current_clicked.hasClass('fc_user_remove') )
				{
					// get the id and the name of list-item
					var user_id		= current_item.find('input[name=user_id]').val(),
						user_name	= current_item.find('#fc_name_'+user_id).val(),
						url			= ADMIN_URL + '/users/delete.php',
						title		= LEPTON_TEXT["MANAGE_USERS"],
						dates		= {
							'user_id':		user_id,
							'leptoken':		getToken()
						},
						message		= LEPTON_TEXT["USERS_CONFIRM_DELETE"]+':<br/><strong>' + user_name + '</strong>';
				}
				else if (current_clicked.hasClass('fc_group_remove') )
				{
					// get the id and the name of list-item
					var user_id		= current_item.find('input[name=group_id]').val(),
						user_name	= current_item.find('input[name=name]').val(),
						url			= ADMIN_URL + '/groups/delete.php',
						dates		= {
							'group_id':		user_id,
							'leptoken':		getToken()
						},
						title		= LEPTON_TEXT["MANAGE_GROUPS"],
						message		= LEPTON_TEXT["GROUPS_CONFIRM_DELETE"]+':<br/><strong>' + user_name + '</strong>';
				}
				else
				{
					return false;
				}
			
				// Define function afterSend
				var afterDelete	= function( data, textStatus, jqXHR )
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
						options.fc_list_add.click();
					}
				}
				dialog_confirm( message, title, url, dates, 'GET', 'HTML', false, afterDelete, element );
			});
			*/
		});
	}
})(jQuery);
