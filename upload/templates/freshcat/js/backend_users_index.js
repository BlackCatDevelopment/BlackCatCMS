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
 

(function ($) {
	$.fn.set_individual_buttons = function (options)
	{
		var defaults =
		{
			toggle_speed:		300,
			individual_class:	'fc_individual',
			active_class:		'fc_active'
		};
		var options = $.extend(defaults, options);
		return this.each(function ()
		{
			var element				= $(this), // .advanced_group
				advanced_label		= element.attr('rel');
			function check_inputs(element)
			{
				if ( element.children('input').size() == element.children('input:checked').size() )
				{
					$('#' + advanced_label).attr('checked' , true).change();
				}
				else if ( element.children('input').size() == element.children('input').not(':checked').size() )
				{
					$('#' + advanced_label).attr('checked' , false).change();
				}
				else
				{
					$('label[for=' + advanced_label + ']').addClass(options.individual_class);
				}
			}

			// Bind advanced-buttons to toggle advanced options on click
			element.prev('span').click( function()
			{
				var advanced_button		= $(this);
				advanced_button.toggleClass(options.active_class);
				if ( advanced_button.hasClass(options.active_class) )
				{
					element.slideUp(0, function()
					{
						element.removeClass('hidden').slideDown(options.toggle_speed);
					});
				}
				else
				{
					element.slideUp(options.toggle_speed , function(){
						element.addClass('hidden');
					});
				}
			});
			// Function to check the inputs in the according advanced-div, if parent button is changed to set all of them true/false
			$('#' + advanced_label).change( function()
			{
				var value	= $(this);
				$('label[for='+advanced_label+']').removeClass(options.individual_class);

				if ( value.is(':checked') )
				{
					element.find('input').attr( 'checked', true ).change();
				}
				else {
					element.children('input').attr( 'checked', false ).change();
				}
			});
			// Function to check the each inputs in the advanced-div, to set the parent button to false/true/individual
			element.children('input').click( function()
			{
				check_inputs(element);
			});
			check_inputs(element);
			element.slideUp(0).addClass('hidden');
		})
	}
})(jQuery);

function validateUserAdd(element)
{
	element.find('input:text').each(function()
	{
		var name = $(this).val();
		var rel = $(this).attr('rel');
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
	$('.fc_advanced_groups').set_individual_buttons();
	$('#fc_list_overview li').fc_set_tab_list();

	// Show submitbuttons only if form is valid
	$('#fc_add_user input').keyup( function()
	{
		validateUserAdd( $('#fc_add_user') );
	});
	$('#fc_add_user input:checkbox').change( function()
	{
		validateUserAdd( $('#fc_add_user') );
	});


	dialog_form( $('#fc_add_user, #fc_add_group'), false, function()
	{
		if ( $('#fc_add_user').size() > 0 )
		{
			var link	= ADMIN_URL + '/users/index.php';
		}
		else if ( $('#fc_add_group').size() > 0 )
		{
			var link	= ADMIN_URL + '/groups/index.php';
		}
		else return;

		// Send infos with ajax and import the new user/group after successful adding to get the forms
		$.ajax(
		{
			type:		'GET',
			url:		link,
			dataType:	'html',
			data:		'leptoken=' + getToken(),

			success:	function(data)
			{
				var index_size		= $('#fc_content_container #fc_list_overview li').size();
				if ( index_size == $(data).find('#fc_list_overview li').size() )
				{
					return_error('An error occured!',process_div);
				}
				else
				{
					$('#fc_content_container #fc_list_overview li, #fc_content_container .fc_user_forms').removeClass('fc_active');
					$(data).find('#fc_list_overview li').each( function()
					{
						var current		= $(this),
							user_id		= current.attr('rel'),
							index		= current.index(),
							new_form	= $(data).find('#' + user_id);

						if ( $('#fc_content_container #'+user_id).size() == 0 )
						{
							// $('.fc_user_forms').not('#'+user_id).slideUp(0);
							if ( index == 0 )
							{
								$('#fc_content_container #fc_list_overview').append( current );
								$('#fc_content_container .fc_list_forms:first').after( new_form );
								$('#fc_content_container #fc_list_overview li:first').fc_set_tab_list().click();

								set_buttons( new_form );

							}
							else if ( index < index_size )
							{
								$('#fc_content_container #fc_list_overview li').eq(index).before( current );
								$('#fc_content_container .fc_list_forms').eq( index + 1 ).before( new_form );
								$('#fc_content_container #fc_list_overview li').eq( index ).fc_set_tab_list().click();

								set_buttons( new_form );
							}
							else
							{
								$('#fc_content_container #fc_list_overview li:last').after( current );
								$('#fc_content_container .fc_list_forms:last').after( new_form );
								$('#fc_content_container #fc_list_overview li:last').fc_set_tab_list().click();

								set_buttons( new_form );
							}
							new_form.find('.fc_advanced_groups').set_individual_buttons();
						}
					});
					
					// Empty the popup to prevent problems with ids
					$('#fc_main_content .ajaxForm').each( function()
					{
						dialog_form( $(this) );
					});
					// Reset the forms
					$('#fc_add_user, #fc_add_group').find('input:reset').click();
				}
			},
	
			error:		function(data)
			{
				return_error(data,process_div);
			}
		});
	});
});