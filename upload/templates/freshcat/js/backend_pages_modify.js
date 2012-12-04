/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author			LEPTON Project
 * @copyright		2012, LEPTON Project
 * @link			http://www.LEPTON-cms.org
 * @license			http://www.gnu.org/licenses/gpl.html
 * @license_terms	please see LICENSE and COPYING files in your package
  *
 */

jQuery(document).ready(function()
{
	$('.fc_toggle_section_block').click(function()
	{
		var current			= $(this),
			current_block	= current.parents('.fc_module_block');

		current_block.toggleClass('fc_active')
		if ( current_block.hasClass('fc_active') )
		{
			current.switchClass( 'icon-eye-blocked-2', 'icon-eye-2' );
			current_block.find('.fc_blocks_content').slideDown(300);
		}
		else
		{
			current.switchClass( 'icon-eye-2', 'icon-eye-blocked-2' );
			current_block.find('.fc_blocks_content').slideUp(300);
		}
	});
	$('#hide_modules').click( function()
	{
		$('.fc_toggle_section_block').removeClass('fc_active').switchClass( 'icon-eye-2', 'icon-eye-blocked-2' );
		$('.fc_blocks_content').slideUp(100);
	});
	$('#show_modules').click( function()
	{	
		$('.fc_toggle_section_block').addClass('fc_active').switchClass( 'icon-eye-blocked-2', 'icon-eye-2' );
		$('.fc_blocks_content').slideDown(100);
	});

	//	If you want to have all sections hidden on startup activate following row
	// $('.module_block').removeClass('active').find('.blocks_content').slideUp(0);

	// Activate tagit for Keywords
	$('#fc_keywords').tagit(
	{
		allowSpaces:		true,
		singleField:		true,
		singleFieldNode:	$('input[name=keywords]')
	});

	// activate sortable function for sections
	$( "#fc_all_blocks" ).sortable({
		//iframeFix:			true,
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
									$('.mceEditor').each(function()
									{
										var id = $(this).prev('textarea').attr('id');
										tinyids[i] = id;
										tinyMCE.execCommand('mceRemoveControl', false, id);
										i++;
									});
								}
								if ( typeof editAreaLoader != 'undefined')
								{
									editAreaLoader.delete_instance('content13');
									//editAreaLoader.execCommand('content13', 'set_editable', false);
								}
								if ( typeof CKEDITOR != 'undefined')
								{
									myinstances = new Array();
									
									//this is the foreach loop
									for(var i in CKEDITOR.instances)
									{
										myinstances[CKEDITOR.instances[i].name] = CKEDITOR.instances[i].getData(); 
										CKEDITOR.instances[i].destroy();
									}
								}
							},
		stop:				function(event, ui)
							{
								if ( typeof tinyids != 'undefined')
								{
									$.each(tinyids, function(key, value) {
										tinyMCE.execCommand('mceAddControl', false, value);
									});
								}
								if ( typeof CKEDITOR != 'undefined')
								{
									for(var i in myinstances)
									{
										CKEDITOR.replace(i).setData(myinstances[i]);
									};
								}
								//editAreaLoader.execCommand('content13', 'set_editable', true);
							},
		update:				function(event, ui)
							{
								var dates			= {
									'sectionid':			$(this).sortable('toArray'),
									'table':				'sections',
									'leptoken':				getToken()
								};
								$.ajax(
								{
									type:		'GET',
									url:		ADMIN_URL + '/pages/ajax_reorder.php',
									dataType:	'json',
									data:		dates,
									cache:		false,
									beforeSend:	function( data )
									{
										data.process	= set_activity( 'Reorder pages' );
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
										$('.popup').dialog('destroy').remove();
										alert(textStatus + ': ' + errorThrown );
									}
								});
								//dialog_ajax(link,dates,beforeSend,false);
							}
	});
	
	// Active Deletebutton for sections
	$('.fc_delete_section').click( function(e)
	{
		e.preventDefault();

		// Check if .popup exists - if not add div.popup before #admin_header
		if ( $('.popup').size() == 0 )
		{
			$('#fc_admin_header').prepend('<div class="popup" />');
		}

		// Add message to .popup to use function set_popup_title();
		$('.popup').html('<p>'+LEPTON_TEXT["SECTION_CONFIRM_DELETE"]+'</p>');

		// Create dates for ajax
		var current		= $(this);
			dates			= {
				'delete_section_id':	current.find('input[name=section_id]').val(),
				'page_id':				$('input[name=page_id]').val(),
				'leptoken':				getToken()
			};

		// Set the array for confirm-buttons
		buttonsOpts = new Array();

		// define button for confirm dialog positive
		buttonsOpts.push(
		{
			'text':		LEPTON_TEXT['YES'], 'click':  function()
				{
					$.ajax(
					{
						type:		'GET',
						context:	current.closest('.fc_module_block'),
						url:		ADMIN_URL + '/pages/ajax_sections_save.php',
						dataType:	'json',
						data:		dates,
						cache:		false,
						beforeSend:	function( data )
						{
							data.process	= set_activity( 'Save section' );
							data.block_name	= dates.block_name
							data.name		= dates.name
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
							$('.popup').dialog('destroy').remove();
							alert(textStatus + ': ' + errorThrown );
						}
					});
				},
			'class':	'submit'
		});
		
		// define button for confirm dialog negative
		buttonsOpts.push(
		{
			'text':		LEPTON_TEXT['NO'], 'click':  function()
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
			title:			LEPTON_TEXT["SECTION_DELETE"],
			buttons:		buttonsOpts
		});

	});

	// Activate block modify
	$('.fc_open_section_modify').click( function()
	{
		var current_modify		= $(this).parents('.fc_section_modify_div_parent').children('.fc_section_modify_div');
		current_modify.toggle(300);
	});

	$('.fc_save_section input').click( function (e)
	{
		e.preventDefault();
		// Create link for ajax
		var current		= $(this).closest('.fc_section_modify_div_parent'),
			dates			= {
				'update_section_id':	current.find('input[name=section_id]').val(),
				'page_id':				$('input[name=page_id]').val(),
				'block':				current.find('select[name=set_block]').val(),
				'name':					current.find('input[name=blockname]').val(),
				'block_name':			current.find('select[name=set_block] option:selected').html(),
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
				'leptoken':				getToken()
			};
		$.ajax(
		{
			type:		'GET',
			context:	current,
			url:		ADMIN_URL + '/pages/ajax_sections_save.php',
			dataType:	'json',
			data:		dates,
			cache:		false,
			beforeSend:	function( data )
			{
				data.process	= set_activity( 'Save section' );
				data.block_name	= dates.block_name
				data.name		= dates.name
			},
			success:	function( data, textStatus, jqXHR  )
			{
				var current	= $(this);

				if ( data.success === true )
				{
					return_success( jqXHR.process , data.message );
					current.closest('.fc_blocks_header').find('.fc_section_header_block strong').text(jqXHR.block_name);
					current.closest('.fc_blocks_header').find('.fc_section_header_name strong').text(jqXHR.name);
					current.children('.fc_section_modify_div').fadeOut(300);
				}
				else {
					return_error( jqXHR.process , data.message);
				}
			},
			error:		function(jqXHR, textStatus, errorThrown)
			{
				alert(textStatus + ': ' + errorThrown );
			}
		});
	});
});