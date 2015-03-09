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

jQuery(document).ready(function()
{
	if ( $('#fc_main_content').size() === 0 )
	{
		$('#fc_content_container').wrapInner('<div id="fc_main_content" />');
	}

	$('.fc_toggle_section_block').click(function()
	{
		var current			= $(this),
			current_block	= current.parents('.fc_module_block'),
			set_cookie		= SESSION + current_block.prop('id');

		current_block.toggleClass('fc_active');
		if ( current_block.hasClass('fc_active') )
		{
			$.cookie( set_cookie, 'open', { path: '/' } );
			current.switchClass( 'icon-eye-blocked-2', 'icon-eye-2' );
			current_block.find('.fc_blocks_content').slideDown(300);
		}
		else
		{
			$.cookie( set_cookie, 'closed', { path: '/' } );
			current.switchClass( 'icon-eye-2', 'icon-eye-blocked-2' );
			current_block.find('.fc_blocks_content').slideUp(300);
		}
	});
	$('.fc_module_block').each(function()
	{
		var $cur		= $(this),
			curId		= $cur.attr('id');
			getCoo		= SESSION + curId;
			if ( typeof $.cookie( getCoo ) !== 'undefined' &&
				$.cookie( getCoo ) == 'closed' )
			{
				$cur.find('.fc_toggle_section_block').click();
			}
	})
	$('#hide_modules').click( function()
	{
		$('.fc_toggle_section_block').removeClass('fc_active').switchClass( 'icon-eye-2', 'icon-eye-blocked-2' );
		$('.fc_module_block').removeClass('fc_active');
		$('.fc_blocks_content').slideUp(100);
	});
	$('#show_modules').click( function()
	{	
		$('.fc_toggle_section_block').addClass('fc_active').switchClass( 'icon-eye-blocked-2', 'icon-eye-2' );
		$('.fc_module_block').addClass('fc_active');
		$('.fc_blocks_content').slideDown(100);
	});
    $('#recreate_af').click( function()
    {
        dates = {
			'page_id'  : $('div#fc_add_module').find('input[name=page_id]').val(),
			'_cat_ajax': 1
		};
        $.ajax(
		{
			type:		'POST',
			url:		CAT_ADMIN_URL + '/pages/ajax_recreate_af.php',
			dataType:	'json',
			data:		dates,
			cache:		false,
			beforeSend:	function( data )
			{
				data.process	= set_activity( 'Recreating file...' );
				data.block_name	= dates.block_name;
				data.name		= dates.name;
			},
			success:	function( data, textStatus, jqXHR  )
			{
				var current	= $(this);
				$('.popup').dialog('destroy').remove();

				if ( data.success === true )
				{
					return_success( jqXHR.process , data.message );
					current.slideUp(300, function() { current.remove(); });
				}
				else {
					return_error( jqXHR.process , data.message);
				}
			},
			error:		function(jqXHR, textStatus, errorThrown)
			{
                if(jqXHR.responseText.indexOf('fc_login_form') != -1) {
                    location.href = CAT_ADMIN_URL + '/login/index.php';
                } else {
					$('.popup').dialog('destroy').remove();
					alert(textStatus + ': ' + errorThrown );
                }
			}
		});
    });

	//	If you want to have all sections hidden on startup activate following row
	// $('.module_block').removeClass('active').find('.blocks_content').slideUp(0);


	// activate sortable function for sections
	$( "#fc_all_blocks" ).sortable({
		handle:				'.fc_section_drag',
		axis:				'y',
		cursor:				'move',
		helper:				'original',
		placeholder:		'fc_sortable_placeholder fc_gradient4 fc_br_all fc_shadow_small',
		forceHelperSize:	true,
		start:				function(event, ui)
							{
								if ( typeof tinyMCE != 'undefined')
								{
									tinyids = new Array();
									var i = 0;
									$('.mce-tinymce').each(function()
									{
										var id = $(this).next('textarea').prop('id');
										tinyids[i] = id;
										tinyMCE.execCommand('mceRemoveEditor', false, id);
										i++;
									});
								}
								/*
								if ( typeof editAreaLoader != 'undefined')
								{
									editAreaLoader.delete_instance('content13');
									//editAreaLoader.execCommand('content13', 'set_editable', false);
								}
								*/
								/*if ( typeof CKEDITOR != 'undefined')
								{
									myinstances = new Array();
									
									//this is the foreach loop
									for(var j in CKEDITOR.instances)
									{
										myinstances[CKEDITOR.instances[j].name] = CKEDITOR.instances[j].getData();
										CKEDITOR.instances[j].destroy();
									}
								}*/
								if(typeof ckeditorOff=='function')ckeditorOff();
							},
		stop:				function(event, ui)
							{

								if ( typeof tinyids != 'undefined')
								{
									$.each(tinyids, function(key, value) {
										tinyMCE.execCommand('mceAddEditor', false, value);
									});
								}
								//editAreaLoader.execCommand('content13', 'set_editable', true);
								if(typeof ckeditorOn=='function')ckeditorOn();
							},
		update:				function(event, ui)
							{
								var dates			= {
									'sectionid':			$(this).sortable('toArray'),
									'table':				'sections'
								};
								dialog_ajax( 'Reorder pages', CAT_ADMIN_URL + '/pages/ajax_reorder.php', dates, 'POST', 'json', false, false, false );
							}
	});
	
	// Active Deletebutton for sections
	$('.fc_delete_section').click( function(e)
	{
		e.preventDefault();

		// Check if .popup exists - if not add div.popup before #admin_header
		if ( $('.popup').size() === 0 )
		{
			$('#fc_admin_header').prepend('<div class="popup" />');
		}

		// Add message to .popup to use function set_popup_title();
		$('.popup').html('<p>'+cattranslate('Do you really want to delete this section?')+'</p>');

		// Create dates for ajax
		var current		= $(this);
			dates			= {
				'delete_section_id':	current.find('input[name=delete_section_id]').val(),
				'_cat_ajax':            1
			};

		// Set the array for confirm-buttons
		buttonsOpts = new Array();

		// define button for confirm dialog positive
		buttonsOpts.push(
		{
			'text':		cattranslate('Yes'), 'click':  function()
				{
					$.ajax(
					{
						type:		'POST',
						context:	current.closest('.fc_module_block'),
						url:		CAT_ADMIN_URL + '/pages/ajax_sections_save.php',
						dataType:	'json',
						data:		dates,
						cache:		false,
						beforeSend:	function( data )
						{
							data.process	= set_activity( 'Save section' );
							data.block_name	= dates.block_name;
							data.name		= dates.name;
						},
						success:	function( data, textStatus, jqXHR  )
						{
							var current	= $(this);
							$('.popup').dialog('destroy').remove();

							if ( data.success === true )
							{
								return_success( jqXHR.process , data.message );
								current.slideUp(300, function() { current.remove(); });
							}
							else {
								return_error( jqXHR.process , data.message);
							}
						},
						error:		function(jqXHR, textStatus, errorThrown)
						{
                            if(jqXHR.responseText.indexOf('fc_login_form') != -1) {
                                location.href = CAT_ADMIN_URL + '/login/index.php';
                            } else {
								$('.popup').dialog('destroy').remove();
								alert(textStatus + ': ' + errorThrown );
                            }
						}
					});
				},
			'class':	'submit'
		});
		
		// define button for confirm dialog negative
		buttonsOpts.push(
		{
			'text':		cattranslate('No'), 'click':  function()
				{
					$('.popup').dialog('destroy').remove();
				},
			'class':	'reset'
		});
		
		// acitvate dialog on popup
		$('.popup').dialog(
		{
			modal:			true,
			show:			'fade',
			closeOnEscape:	true,
			title:			cattranslate('Delete section'),
			buttons:		buttonsOpts
		});

	});

	// Activate block modify
	$('.fc_open_section_modify').click( function()
	{
		var current_modify		= $(this).parents('.fc_section_modify_div_parent').children('.fc_section_modify_div');
		current_modify.toggle(300);
	});

	$('.fc_modify_section input[type=submit]').click( function (e)
	{
		e.preventDefault();
		// Create link for ajax
		var current		= $(this).closest('.fc_section_modify_div_parent'),
			dates			= {
				'update_section_id':	current.find('input[name=update_section_id]').val(),
				'page_id':				$('input[name=page_id]').val(),
				'set_block':			current.find('select[name=set_block]').val(),
				'blockname':			current.find('input[name=blockname]').val(),
				'day_from':				current.find('input[name=day_from]').val(),
				'month_from':			current.find('input[name=month_from]').val(),
				'year_from':			current.find('input[name=year_from]').val(),
				'hour_from':			current.find('input[name=hour_from]').val(),
				'minute_from':			current.find('input[name=minute_from]').val(),
		
				'day_to':				current.find('input[name=day_to]').val(),
				'month_to':				current.find('input[name=month_to]').val(),
				'year_to':				current.find('input[name=year_to]').val(),
				'hour_to':				current.find('input[name=hour_to]').val(),
				'minute_to':			current.find('input[name=minute_to]').val(),
				'_cat_ajax':            1
			};
		$.ajax(
		{
			type:		'POST',
			context:	current,
			url:		CAT_ADMIN_URL + '/pages/ajax_sections_save.php',
			dataType:	'json',
			data:		dates,
			cache:		false,
			beforeSend:	function( data )
			{
				data.process	= set_activity( 'Save section' );
				data.block_name	= dates.block_name;
				data.name		= dates.name;
			},
			success:	function( data, textStatus, jqXHR  )
			{
				var current	= $(this);

				if ( data.success === true )
				{
					return_success( jqXHR.process , data.message );
					current.closest('.fc_blocks_header').find('.fc_section_header_block strong').text(data.updated_block);
					current.closest('.fc_blocks_header').find('.fc_section_header_name strong').text(data.updated_section.name);
					current.children('.fc_section_modify_div').fadeOut(300);
				}
				else {
					return_error( jqXHR.process , data.message);
				}
			}
		});
	});
});