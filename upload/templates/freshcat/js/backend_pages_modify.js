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
		handle:				'.fc_section_drag',
		axis:				'y',
		cursor:				'move',
		helper:				'original',
		placeholder:		'ui-sortable-placeholder ui-corner-all',
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
							},
		stop:				function(event, ui)
							{
								if ( typeof tinyids != 'undefined')
								{
									$.each(tinyids, function(key, value) {
										tinyMCE.execCommand('mceAddControl', false, value);
									});
								}
								//editAreaLoader.execCommand('content13', 'set_editable', true);
							},
		update:				function(event, ui)
							{
								var dates		= $(this).sortable("serialize")+'&table=sections';
								var link		= ADMIN_URL+'/pages/reorder.php';
								var beforeSend	= function ()
								{
									process_div = set_activity();
								}
								dialog_ajax(link,dates,beforeSend,false);
							}
	});
	
	// Active Deletebutton for sections
	$('.fc_delete_section').click( function()
	{
		// Get page_id and section_id
		var section_id		= $(this).children('input[name=section_id]').val();
		var page_id			= $('input[name=page_id]').val();

		// define message -> there should be a language 
		var message			= '<span class="popup_header">'+LEPTON_TEXT["SECTION_DELETE"]+'</span><p>'+LEPTON_TEXT["SECTION_CONFIRM_DELETE"]+'</p>';

		// define link
		var link			= ADMIN_URL+'/pages/sections_save.php?delete_section_id='+section_id+'&page_id='+page_id;

		// define afterSend function to remove deleted section if everything worked fine
		var afterSend		= function ()
		{
			$(this).slideUp(300, function() { $(this).remove(); });
		}
		// define element to be connected after successful ajaxRequest...
		var jQcontext		= $(this).closest('.fc_module_block');

		dialog_confirm(message,link,false,afterSend,jQcontext);
	});

	// Activate block modify
	$('.fc_open_section_modify').click( function()
	{
		var current_modify		= $(this).parents('.fc_section_modify_div_parent').children('.fc_section_modify_div');
		current_modify.toggle(300);
	});

	$('.fc_save_section input').click( function ()
	{
		var current		= $(this).closest('.fc_section_modify_div_parent');

		var section_id	= current.find('input[name=section_id]').val(),
			page_id		= $('input[name=page_id]').val(),
			block		= current.find('select[name=set_block]').val(),
			block_name	= current.find('select[name=set_block] option:selected').html(),
			name		= current.find('input[name=blockname]').val(),

			day_from	= current.find('input[name=day_from]').val(),
			month_from	= current.find('input[name=month_from]').val(),
			year_from	= current.find('input[name=year_from]').val(),
			hour_from	= current.find('input[name=hour_from]').val(),
			minute_from	= current.find('input[name=minute_from]').val(),

			day_to		= current.find('input[name=day_to]').val(),
			month_to	= current.find('input[name=month_to]').val(),
			year_to		= current.find('input[name=year_to]').val(),
			hour_to		= current.find('input[name=hour_to]').val(),
			minute_to	= current.find('input[name=minute_to]').val(),

			link			= ADMIN_URL+'/pages/sections_save.php',
			dates			= 'update_section_id='+section_id+'&page_id='+page_id+'&block='+block+'&name='+name+'&day_from='+day_from+'&month_from='+month_from+'&year_from='+year_from+'&hour_from='+hour_from+'&minute_from='+minute_from+'&day_to='+day_to+'&month_to='+month_to+'&year_to='+year_to+'&hour_to='+hour_to+'&minute_to='+minute_to,
			beforeSend	= function ()
				{
					process_div = set_activity();
				},
			afterSend		= function ()
				{
					current.closest('.fc_blocks_header').find('.fc_section_header_block strong').html(block_name);
					current.closest('.fc_blocks_header').find('.fc_section_header_name strong').html(name);
					current.children('.fc_section_modify_div').fadeOut(300);
				};
		dialog_ajax(link,dates,beforeSend,afterSend);
		return false;
	});

});