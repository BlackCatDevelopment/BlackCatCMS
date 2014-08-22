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
/**
 * check the checkboxes in an according div (given by class set_advanced___) and set an indivdual class if they are not equal. if equal according input gets same value
 *
 * @type plugin
 * @param  string  standard_class - standard class when all values are equal
 * @param  string  individual_class - individual class when values are different
 *
 **/
(function ($) {
	$.fn.set_individual_buttons = function (options)
	{
		var defaults =
		{
			standard_class:		'fc_checkbox_jq',
			individual_class:	'fc_checkbox_ind'
		};
		var options = $.extend(defaults, options);
		// Function to check whether all children inputs are checked=true, checked=false or different checked
		var check_inputs	= function(element)
		{
			var advanced			= match_class_prefix( 'set_advanced___', element ),
				advanced_div		= $('#' + advanced);

			if ( advanced_div.children('input').size() == advanced_div.children('input:checked').size() )
			{
				element.prop('checked' , true).addClass(options.standard_class).removeClass(options.individual_class);
			}
			else if ( advanced_div.children('input').size() == advanced_div.children('input').not(':checked').size() )
			{
				element.prop('checked' , false).addClass(options.standard_class).removeClass(options.individual_class);
			}
			else
			{
				element.prop('checked' , true).addClass(options.individual_class).removeClass(options.standard_class);
			}
		};
		return this.each(function ()
		{
			var element				= $(this),
				advanced			= match_class_prefix( 'set_advanced___', element ),
				advanced_div		= $('#' + advanced);

			// Function to check the each inputs in the advanced-div, to set the parent button to false/true/individual
			advanced_div.children('input').click( function()
			{
				check_inputs( element );
			});
			element.change( function()
			{
				var checked		= element.is(':checked');
				advanced_div.children('input').prop('checked' , checked);
				check_inputs( element );
			});
			// Initial calling of function
			check_inputs( element );
		});

	};
})(jQuery);

/**
 * activate click on a list element to get all contents of a group/user
 *
 * @type plugin
 * @param  string  get_url - url where to get the values
 * @param  string  activity_message - Message that will be shown in the activity div
 * @param  string  get_id - name of input element that contains the group_id/user_id
 *
 **/
(function ($) {
	$.fn.set_list_click = function (options)
	{
		var defaults	=
		{
			get_url:			CAT_ADMIN_URL + '/groups/ajax_get_group.php',
			activity_message:	'Loading group',
			addOnly:			$('.fc_addGroup'),
			modifyOnly:			$('.fc_modifyGroup'),
			get_id:				'group_id'
		};
		var options		= $.extend(defaults, options);
		return this.each(function ()
		{
			var element				= $(this);

			element.click( function(e)
			{
				e.preventDefault();
				var current		= $(this),
					dates		= {
						'id':			current.children('input[name=' + options.get_id + ']').val(),
						'_cat_ajax':    1
					};
				$.ajax(
				{
					type:		'POST',
					context:	current,
					url:		options.get_url,
					dataType:	'JSON',
					data:		dates,
					cache:		false,
					beforeSend:	function( data )
					{
						data.process	= set_activity( options.activity_message );
					},
					success:	function( data, textStatus, jqXHR  )
					{
						var current			= $(this),
							current_ul		= current.closest('ul');

						current_ul.children('li').not(current).removeClass('fc_active');
						$('#fc_list_add').removeClass('fc_active');
						current.addClass('fc_active');

						if ( data.success === true )
						{
							options.addOnly.hide();
							options.modifyOnly.show();
							var checkboxes	= $('#fc_Group_form, #fc_User_form').find('input:checkbox');
							checkboxes.not('[id*=fc_Group_m_], [id*=fc_Group_t_]').prop( 'checked', false );
							checkboxes.filter('[id*=fc_Group_m_], [id*=fc_Group_t_]').prop( 'checked', false );

							if ( options.get_id == 'group_id' )
							{
								$('#fc_Group_name').val(data.name);
								$('#fc_Group_group_id').val(data.group_id);

								$.each(data.system_permissions, function(index, value)
								{
									$('#fc_Group_' + value).prop( {checked: true} );
								});
								$.each(data.module_permissions, function(index, value)
								{
									$('#fc_Group_m_' + value).prop( {checked: true});
								});
								$.each(data.template_permissions, function(index, value)
								{
									$('#fc_Group_t_' + value).prop( {checked: true});
								});

                                $('div#fc_members').html(data.members);
								//$('input[class*=set_advanced___]').unbind().set_individual_buttons();
							}
							else {
								$('#fc_User_name').val( data.username ).prop( 'name', data.username_fieldname );
								$('#fc_User_user_id').val( data.user_id );
								$('input[name=username_fieldname]').val( data.username_fieldname );
								$('#fc_User_display_name').val( data.display_name );
								$('#fc_User_email').val( data.email );
								$('#fc_User_password, #fc_User_password2').val('');
								$('#fc_User_active_user').prop( {checked: data.active});
                                $('#fc_init_page').val(data.initial_page);
                                $('#fc_init_page_param').val( data.initial_page_param );
								$('#fc_User_home_folder option').prop( {selected: false}).filter('[value="' + data.home_folder + '"]').prop( {selected: true});
								$.each(data.groups, function(index, value)
								{
									$('#fc_User_groups_' + value).prop( {checked: true});
								});
                            }
							return_success( jqXHR.process , data.message);
						}
						else {
							return_error( jqXHR.process , data.message);
						}
					}
				});
			});
		});
	};
})(jQuery);

/**
 * check values for user if they fix to the challenges
 *
 * @type plugin
 * @param  string  get_url - url where to get the values
 * @param  string  activity_message - Message that will be shown in the activity div
 * @param  string  get_id - name of input element that contains the group_id/user_id
 *
 **/
function validateUserAdd(element)
{
	element.find('input:text').each(function()
	{
		var name = $(this).val();
		var rel = $(this).prop('rel');
		if( rel!='email' && name.length > rel )
		{
			$(this).removeClass('fc_invalid').addClass('fc_valid');
		}
		else if ( rel=='email' && isValidEmailAddress(name) )
		{
			$(this).removeClass('fc_invalid').addClass('fc_valid');
		}
		else
		{
			$(this).addClass('fc_invalid').removeClass('fc_valid');
		}
	});

	var pw1 = element.find('input:password').eq(0).val(),
		pw2 = element.find('input:password').eq(1).val();
	if( ( pw1 == pw2 ) && ( pw1.length > 5 ) )
	{
		// is valid
		element.find('input:password').removeClass('fc_invalid').addClass('fc_valid');
	}
	else
	{
		element.find('input:password').addClass('fc_invalid').removeClass('fc_valid');
	}

	if ( element.find('#fc_group input:checked').size() > 0 )
	{
		element.find('#fc_group').addClass('fc_valid').removeClass('fc_invalid');
	}
	else
	{
		element.find('#fc_group').removeClass('fc_valid').addClass('fc_invalid');
	}
	if ( element.find('.fc_invalid').size() > 0 )
	{
		$('.ui-dialog-buttonpane .submit').fadeOut(700);
	}
	else
	{
		$('.ui-dialog-buttonpane .submit').fadeIn(700);
	}
}

jQuery(document).ready(function()
{
	$('input[class*=set_advanced___]').set_individual_buttons();

	$('.fc_group_list').children('li').set_list_click();
	$('.fc_user_list').children('li').set_list_click(
	{
		get_url:			CAT_ADMIN_URL + '/users/ajax_get_user.php',
		activity_message:	'Loading user',
		addOnly:			$('.fc_addUser'),
		modifyOnly:			$('.fc_modifyUser'),
		get_id:				'user_id'
	});

	$('#fc_list_add').click( function(e)
	{
		e.preventDefault();

		var current		= $(this);

		$('#fc_list_overview').children('li').removeClass('fc_active');
		current.addClass('fc_active');

		$('.fc_modifyGroup, .fc_modifyUser').hide();
		$('.fc_addGroup, .fc_addUser').show();

		$('#fc_Group_form, #fc_User_form').find('input:checkbox').prop( 'checked', false );
		$('#fc_User_form select > option:first').prop( 'selected', true );
        $('#fc_init_page > option[value="start/index.php"]').prop( 'selected', true );
        $('#fc_init_page').val("start/index.php");
		$('#fc_Group_name, #fc_Group_group_id, #fc_User_form input:text, #fc_User_form input:password').val('').filter('#fc_Group_name').focus();
	}).click();

	$('#fc_Group_form input:reset, #fc_User_form input:reset').click( function(e)
	{
		e.preventDefault();
		$('#fc_lists_overview').find('.fc_active').click();
	});

	$('input[name=addGroup], input[name=saveGroup]').click( function(e)
	{
		e.preventDefault();
		var current					= $(this),
			currentForm				= current.closest('form'),
			dates					= {
				'_cat_ajax':        1
			};
			templates				= new Array();
			modules					= new Array();
		dates[current.prop('name')]	= current.val();
		currentForm.find('input[type=checkbox]:checked, input[type=text], #fc_Group_group_id').map( function()
		{
			var fieldname	= $(this).prop('name'),
				value		= $(this).val();
			if ( fieldname == 'module_permissions[]' )
			{
				return dates['module_permissions']		= modules.push( value );
			}
			else if( fieldname == 'template_permissions[]' ) {
				return dates['template_permissions']	= templates.push( value );
			}
			else {
				return dates[fieldname]					= value;
			}
		});
		dates['module_permissions']			= modules;
		dates['template_permissions']		= templates;
		console.log(dates);
		$.ajax(
		{
			type:		'POST',
			context:	current,
			url:		CAT_ADMIN_URL + '/groups/ajax_save_group.php',
			dataType:	'JSON',
			data:		dates,
			cache:		false,
			beforeSend:	function( data )
			{
				data.process	= set_activity( 'Saving group' );
			},
			success:	function( data, textStatus, jqXHR  )
			{
				if ( data.success === true )
				{
					return_success( jqXHR.process , data.message);

					if ( data.action == 'saved' )
					{
						$('#fc_list_overview').children('.fc_active').children('.fc_groups_name').text(data.name);
					}
					else {
						$('<li class="fc_group_item icon-users fc_border fc_gradient1 fc_gradient_hover"><span class="fc_groups_name">' + data.name + '</span><input type="hidden" name="group_id" value="' + data.id + '" /></li>').appendTo('#fc_list_overview').set_list_click().click();
					}
				}
				else {
					return_error( jqXHR.process , data.message);
				}
			}
		});
	});
	$('input[name=addUser], input[name=saveUser]').click( function(e)
	{
		e.preventDefault();
		var current					= $(this),
			currentForm				= current.closest('form'),
			dates					= {
				'_cat_ajax':    1,
				'home_folder':	$('#fc_User_home_folder option:selected').val()
			},
			groups					= new Array();

		dates[current.prop('name')]	= current.val();
		currentForm.find('input[type=checkbox]:checked, input:text, input:password, select, #fc_User_user_id, #fc_User_fieldname').map( function()
		{
			var fieldname	= $(this).prop('name') == 'groups[]' ? 'groups' : $(this).prop('name');
			return dates[fieldname]	= $(this).prop('name') == 'groups[]' ? groups.push( $(this).val() ) : $(this).val();
		});
		dates['groups']		= groups;
		$.ajax(
		{
			type:		'POST',
			context:	current,
			url:		CAT_ADMIN_URL + '/users/ajax_save_user.php',
			dataType:	'JSON',
			data:		dates,
			cache:		false,
			beforeSend:	function( data )
			{
				data.process	= set_activity(cattranslate('Saving user'));
			},
			success:	function( data, textStatus, jqXHR  )
			{
				if ( data.success === true )
				{
					return_success( jqXHR.process , data.message);

					if ( data.action == 'saved' )
					{
						$('#fc_User_fieldname').val(data.username_fieldname);
						$('#fc_User_name').prop('name',data.username_fieldname);
						$('#fc_list_overview').children('.fc_active').children('.fc_display_name').text(data.display_name);
						$('#fc_list_overview').children('.fc_active').children('.fc_list_name').text(data.user_name);
					}
					else {
						$('<li class="fc_group_item icon-user fc_border fc_gradient1 fc_gradient_hover"><span class="fc_display_name">' + data.display_name + '</span><br/><span class="fc_list_name">' + data.username + '</span><input type="hidden" name="user_id" value="' + data.id + '" /></li>').appendTo('#fc_list_overview').set_list_click(
							{
								get_url:			CAT_ADMIN_URL + '/users/ajax_get_user.php',
								activity_message:	'Loading user',
								addOnly:			$('.fc_addUser'),
								modifyOnly:			$('.fc_modifyUser'),
								get_id:				'user_id'
							}).click();
					}
				}
				else {
					return_error( jqXHR.process , data.message);
				}
			}
		});
	});

	$('#fc_removeGroup, #fc_removeUser').click( function(e)
	{
		e.preventDefault();
		var current		= $(this),
			kind		= current.prop('id') == 'fc_removeUser' ? 'user' : 'group',
			dates		= {
				'id':			kind == 'group' ? $('#fc_Group_group_id').val() : $('#fc_User_user_id').val()
			},
			current_li	= $('#fc_list_overview').children('.fc_active'),
			afterSend	= function( data, textStatus, jqXHR )
			{
				var current		= $(this);
				if ( $('#fc_list_overview').children('li').size() == 1 ) {
					$('#fc_list_add').click();
				}
				else if ( current.is(':last-child') )
				{
					current.prev('li').click();
				}
				else {
					current.next('li').click();
				}
				current.remove();
			},
			url		= kind == 'group' ? '/groups/ajax_delete_group.php' : '/users/ajax_delete_user.php';
		    dialog_confirm( 'Do you really want to delete this ' + kind + '?', 'Removing '+kind, CAT_ADMIN_URL + url, dates, 'POST', 'JSON', false, afterSend, current_li );
	});

	$('ul.fc_groups_tabs').find('a').click( function(e)
	{
		e.preventDefault();
		var current	= $(this),
			buttons	= current.closest('ul').find('a').not(current),
			rel		= current.prop('href'),
			tabs	= $('.fc_toggle_tabs');

		buttons.removeClass('fc_active');
		current.addClass('fc_active');
        // hide all
        tabs.hide();
        $(rel.substring(rel.indexOf('#'))).show();
	}).filter(':first').click();
});