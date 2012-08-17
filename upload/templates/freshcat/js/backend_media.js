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

// This function get the current .media_folder in which an element is active. If there is no active element the root folder is returned
function get_active_media()
{
	if ( $('#fc_media_browser .fc_media_folder_active').size() > 0 )
	{
		return $('#fc_media_browser .fc_media_folder_active');
	}
	else if ( $('#fc_media_browser .fc_active').size() > 0 )
	{
		// Check if the activated item is not a folder to choose the current directory
		if ( $('#fc_media_browser .fc_active').hasClass('fc_filetype_image') )
		{
			return  $('#fc_media_browser .fc_active').closest('.fc_media_folder');
		}
		// If the activated type is a folder choose the next directory
		else
		{
			return $('#fc_media_browser .fc_active').closest('.fc_media_folder').next('.fc_media_folder');
		}
	}
	// If no file was activated choose the root directory
	else
	{
		$('.fc_media_folder:first').addClass('fc_media_folder_active');
		return $('.fc_media_folder:first');
	}
}

// This function send an ajaxRequest to media/rename.php to save the new name of a folder or file
function save_name( current_active )
{
	var new_name				= current_active.find('input[name=rename]').val(),
		file_path				= current_active.closest('.fc_media_folder').find('input[name=folder_path]').val(),
		rename_file				= current_active.find('input[name=load_url]').val(),
		link					= ADMIN_URL + '/media/rename.php',
		dates					= 'file_path=' + file_path + '&rename_file=' + rename_file + '&new_name=' + new_name,
		beforeSend				= function ()
									{
										// Add a fc_loader-icon to the current folder and delete all subfolders from the view
										current_active.closest('.fc_media_folder').prepend('<li class="fc_loader" />');
										current_active.closest('.fc_media_folder').nextAll('.fc_media_folder').remove();
									},
		afterSend				= function ()
									{
										// Reload the current selected folder to see, whether everything worked fine and to get automatically the right order
										// the previous folder or file is currently not selected again - could be added later
										var current_active	= $(this).closest('.fc_media_folder');
										current_active.find('.fc_loader').remove();
										reload_folder( current_active );
									};
	dialog_ajax( link, dates, beforeSend, afterSend, current_active, 'POST' );
}

// This function send a ajaxRequest to get_contents.php to get all files from the selected folder
function reload_folder( current_active )
{
	var folder_path				= current_active.find('input[name=folder_path]').val(),
		link					= ADMIN_URL + '/media/get_contents.php',
		dates					= 'load_url=' + folder_path,
		afterSend				= function (data)
									{
										var current_active	= $(this);
										// Reload the current selected folder to see, whether everything worked fine and to get automatically the right order
										// the previous folder or file is currently not selected again - could be added later
										// clear and fade out media_info
										$('#fc_media_info').fadeOut(300).html('');
										// Get the contents from data
										current_active.html( $(data).find('.fc_media_folder').html() );
										current_active.prev('.fc_media_folder').find('.fc_open_folder').removeClass('fc_open_folder').addClass('fc_active');

										// Scroll to the very right to put the new loaded files into viewable area
										scrollToRight();

										// Activate all click-events to the new added list
										set_media_functions();

										// Fix names again to fit to the list
										current_active.find('.fc_name_short p').smartTruncation({ 'truncateCenter' : true });
									};
	if ( current_active.find('.fc_active').size() > 0 )
	{
		var dates	= dates + '&open_folder=' + current_active.find('.fc_active').children('input[name=load_url]').val();
	}
	dialog_ajax( link, dates, false, afterSend, current_active, 'POST' );
}

// Function to automatically scroll to the very right if an item was clicked
function scrollToRight()
{
	var browser_width = $('#fc_media_browser').width();

	$('#fc_media_browser').animate({'scrollLeft': browser_width},0);

}

// Function to (re)activate the clickevent of items in the browser
function set_media_functions()
{
	// Set click for all "non-folder"-items to set them active and show info
	$('.fc_filetype_image').not('.fc_no_content, .fc_save_rename').unbind().click(function()
	{
		// Store current item to variable
		var current = $(this);

		// Remove active-Class from all other list-items
		current.closest('.fc_media_folder').prevAll('.fc_media_folder').find('.fc_active').addClass('fc_open_folder').removeClass('fc_active');
		current.closest('.fc_media_folder').find('.fc_active').removeClass('fc_active');
		current.closest('.fc_media_folder').find('.fc_open_folder').removeClass('fc_open_folder');


		// Than add active-Class to the clicked item
		current.addClass('fc_active');

		// Remove all sub-folders to show the media-info next to the clicked item
		$('.fc_filetype_image.fc_active').closest('.fc_media_folder').nextAll('.fc_media_folder').remove();

		// Get count of currently shown media-folders get the right position for media-info
		var folder_count = $('.fc_media_folder').size();
		$('#fc_media_info').css({left: folder_count*251+'px'});

		// Clear content of #media_info and add the fileinfo of the clicked item
		$('#fc_media_info').html('').fadeIn(300);
		current.children('.fc_file_info').clone().appendTo('#fc_media_info');
		set_media_buttons($('#fc_media_info'));

		// Push info into users view
		scrollToRight();
	});


	// Set click for all folder-items to hide info and load contents of the clicked directory via ajax
	$('.fc_filetype_folder').unbind().click(function()
	{
		var current_folder = $(this);

		// Remove active-Class from all other list-items
		current_folder.closest('.fc_media_folder').prevAll('.fc_media_folder').find('.fc_active').addClass('fc_open_folder').removeClass('fc_active');
		current_folder.closest('.fc_media_folder').find('.fc_active').removeClass('fc_active');
		current_folder.closest('.fc_media_folder').find('.fc_open_folder').removeClass('fc_open_folder');

		// Store current item to variable
		current_folder.addClass('fc_active');

		// Create link for ajax
		var link			= ADMIN_URL+'/media/get_contents.php',
			load_url		= current_folder.children('input[name=load_url]').val(),
			folder_path		= current_folder.closest('.fc_media_folder').find('input[name=folder_path]').val(),
			dates			= 'load_url=' + load_url + '&folder_path=' + folder_path,
			beforeSend		= function ()
								{
									current_folder			= $(this);
									// Remove all sub-folders and hide&clear #media_info
									$('#fc_media_info').fadeOut(300).html('');
									current_folder.closest('.fc_media_folder').nextAll('.fc_media_folder').remove();
									// Get count of currently shown media-folders get the right position for media-info
									var folder_count		= $('.fc_media_folder').size();
									current_folder.closest('.fc_media_folder').after('<ul class="fc_media_folder"><li class="fc_loader"></li></div>');
									$('.fc_media_folder:last').css({left: folder_count * 251 + 'px'});
								},
			afterSend		= function (data)
								{
									// Check that no image was clicked, while loading!
									if( $('.fc_filetype_folder.fc_active').size() > 0 )
									{
										// Get count of currently shown media-folders get the right position for media-info
										var folder_count	= $('.fc_media_folder').size();
										// Add folder-content of data to the last media_folder
										$('.fc_media_folder_active').removeClass('fc_media_folder_active');
										$('.fc_media_folder:last').html( $(data).find('.fc_media_folder').html() ).addClass('fc_media_folder_active');
										$('#fc_media_info').css({left: ( folder_count + 1 ) * 251 + 'px'});
										// Fix names again to fit to the list
										$('.fc_media_folder:last .fc_name_short p').smartTruncation({ 'truncateCenter' : true });
										// Push info into users view
										scrollToRight();
										// Activate all click-events to the new added list
										set_media_functions();
									}
								};
		dialog_ajax( link, dates, beforeSend, afterSend, current_folder, 'POST' );
	});

	$('.fc_media_folder').unbind().click( function()
	{
		$('.fc_media_folder_active').removeClass('fc_media_folder_active');
		$(this).addClass('fc_media_folder_active');
	});
}

function set_media_buttons(element)
{
	element.find('.fc_delete_file').unbind().click(function()
	{
		// Check if any item is active
		if ( $('#fc_media_browser .fc_active').size() == 0 )
		{
			// if not deactive the button
			$(this).addClass('fc_inactive_button');
		}
		else
		{
			// Store current active item in a variable
			var current_active	= $('#fc_media_browser .fc_active');
			// Get the previous name of the file that is supposed to be deleted (files and folders has to be seperated!)
			if ( current_active.hasClass('fc_filetype_image') )
			{
				var name		= '<br/><strong>'+current_active.find('.fc_name_short').text()+'</strong>',
					message		= LEPTON_TEXT['MEDIA_CONFIRM_DELETE_FILE'].replace( /\{name\}/g, name );
			}
			else
			{
				var name		= '<br/><strong>' + current_active.find('.fc_name_short').text() + '</strong>',
					message		= LEPTON_TEXT['MEDIA_CONFIRM_DELETE_DIR'].replace( /\{name\}/g, name );
			}
			// Find the folderpath of chosen directory
			var file_path		= current_active.closest('.fc_media_folder').find('input[name=folder_path]').val(),
				file			= current_active.find('input[name=load_url]').val(),
				link			= ADMIN_URL+'/media/delete.php?file_path='+file_path+'&file='+file,
				beforeSend		= function()
					{
						// Add fc_loader to current folder
						current_active.closest('.fc_media_folder').prepend('<li class="fc_loader" />');
					}
				afterSend		= function()
					{
						// set the parent folder to active again
						current_active	= $(this);
						current_active.closest('.fc_media_folder').prev('.fc_media_folder').find('.fc_open_folder').addClass('fc_active').removeClass('fc_open_folder');
						// If a folder was deleted also remove the sub-folder from view
						if(current_active.hasClass('fc_filetype_folder'))
						{
							current_active.closest('.fc_media_folder').nextAll('.fc_media_folder').remove();
							//current_active.closest('.media_folder').remove();
						}
						// If a file was deleted remove the media info for this file
						else
						{
							$('#fc_media_info').fadeOut(300).html('');
						}
						// Reload the current folder
						reload_folder( current_active.closest('.fc_media_folder') );
					};
			dialog_confirm( message, link, beforeSend, afterSend, current_active );
		}
	});

	element.find('.fc_rename_file').unbind().click(function()
	{
		// Check if any item is active
		if ($('#fc_media_browser .fc_active').size()==0)
		{
			// if not deactive the button
			$(this).addClass('fc_inactive_button');
		}

		else
		{
			// unbind the list-items to prevent unwanted actions while renaming
			$('.fc_filetype_folder, .fc_filetype_image').unbind();

			// Store current active item in a variable
			var current_active = $('#fc_media_browser .fc_active');

			// Get the previous name of the file that is supposed to be renamed (files and folders has to be seperated!)
			if (current_active.hasClass('fc_filetype_image'))
			{
				var old_name = current_active.find('.fc_filename').text();
			}
			else
			{
				var old_name = current_active.find('p').text();
			}

			// add the input field to the list item that should be renamed
			current_active.append('<div class="fc_input_rename_file"><input type="text" name="rename" value="' + old_name + '" /><span class="fc_save_rename">&nbsp;</span></div>');

			// animate and focus the input element
			current_active.children('.fc_input_rename_file').slideUp(0,function()
			{
				current_active.children('.fc_name_short').slideUp(200);
				current_active.children('.fc_input_rename_file').slideDown(200, function()
				{
					// Focus the input element
					current_active.find('input[name=rename]').focus();

					// Activate the keydown event to save the name if "enter" is pressed
					current_active.find('input[name=rename]').keydown(function(event) {
						if (event.keyCode == '13')
						{
							save_name(current_active);
						}
					});

					// Bind the mouseover event to the save-button to add a class so the script can check on focusout whether the name shall be saved or not
					current_active.find('.fc_save_rename').mouseenter(function()
					{
						// Tell the script that the name is currently updated
						current_active.addClass('fc_name_update_process');
					}).mouseleave(function()
					{
						// Tell the script that the name is not currently updated
						current_active.removeClass('fc_name_update_process');
					});

					// Bind save-button to save the new name on click event
					current_active.find('.fc_save_rename').click(function()
					{
						save_name(current_active);
					});

					// Bind focus out to the input-field
					current_active.find('input[name=rename]').focusout(function()
					{
						// Set timeout so the clickevent on .save_rename works
						if (!current_active.hasClass('fc_name_update_process'))
						{
							// show name again and remove the input field
							$('.fc_name_short').slideDown(0);
							$('.fc_input_rename_file').remove();

							// Activate the click events for list items again
							set_media_functions();
						}
					});
				});
			});
		}
	});
}

function copy_upload_field( upload_field )
{
	var current_field		= upload_field.children('input[type=file]');

	// bind fc_upload_choose to select file
	upload_field.children('.fc_upload_choose').click(function()
	{
		current_field.click();
	});

	upload_field.find('.fc_upload_close').unbind().click( function()
	{
		if( $('#fc_media_index_upload').find('input[type=file]').size() > 2)
		{
			// Remove field if there are still more than one left
			upload_field.remove();
			$('.fc_upload_fields:last').removeClass('inactive');
		}
		else
		{
			upload_field.removeClass('inactive');
			// reset value of the last input
			upload_field.find('input[type=file]').val('');
			upload_field.find('.fc_upload_file').text('No file selected...');

			// reset checkboxes for unzipping (and hiding those options)
			upload_field.find('.fc_upload_zip').addClass('hidden');

			// Reset functions for current field
			copy_upload_field( upload_field );
		}
	});

	current_field.unbind().change( function()
	{
		// get all values needed later
		var file			= current_field.val(),
			extension		= file.substr( (file.lastIndexOf('.') + 1) ),
			allowed_ext		= current_field.attr('accept').split('\|');

		if ( jQuery.inArray( extension, allowed_ext ) > -1 )
		{
			if ( !upload_field.hasClass('inactive') )
			{
				add_new_upload_field ( upload_field )
			}
			// Set text inside grafic input to value of the upload field
			upload_field.find('.fc_upload_file').text(file.substr( (file.lastIndexOf('\\') +1) ));
		
			// Check extesion and bind some effects to different kinds
			switch(extension)
			{
				case 'zip':
				case 'rar':
				case 'gz':
					upload_field.find('.fc_upload_zip').removeClass('hidden');
				break;
				default:
					upload_field.find('.fc_upload_zip').addClass('hidden');
				break;
			}
		}
	});

	// Reset togglefunction for unzipping
	set_buttons( upload_field.find( '.fc_toggle_element' ) );
}

function add_new_upload_field ( upload_field )
{
	if ( upload_field == false )
	{
		upload_field	= $('#fc_add_upload_field');
		var field_size		= 0;
	}
	else
	{
		upload_field.addClass('inactive');
		var field_size		= parseInt( $('.fc_upload_fields:last').find('input[type=hidden]:last').val() ) + 1;
	}
	
	var new_field		= $('#fc_upload_field_add').clone().appendTo( upload_field.parent() ).removeAttr('id').addClass('fc_upload_fields').removeClass('hidden');

	new_field.find( 'input[name=upload_counter_replace]' ).val( field_size ).attr( 'name', 'upload_counter[]' );
	new_field.find( 'input[name=upload_]' ).attr( 'name', 'upload_' + field_size );
	new_field.find( '#unzip_' ).attr( 'name', 'unzip_' + field_size ).attr( 'id', 'unzip_' + field_size ).addClass( 'fc_checkbox_jq fc_toggle_element' ).attr( 'rel' , 'fc_delete_zip_div_' + field_size ).prev('label').attr( 'for', 'unzip_' + field_size );
	new_field.find( '#delete_zip_' ).attr( 'name', 'delete_zip_' + field_size ).attr( 'id', 'delete_zip_' + field_size ).addClass('fc_checkbox_jq').prev('label').attr( 'for', 'delete_zip_' + field_size ).parent('div').attr( 'id', 'fc_delete_zip_div_' + field_size );

	// Set function for new added field
	copy_upload_field( new_field );

	set_buttons( new_field );
}

jQuery(document).ready(function()
{
	$('#fc_unzip_0').fc_toggle_element();

	$('#fc_upload_button').click(function()
	{
		$('.fc_upload_fields').remove();
		add_new_upload_field( false );
	});

	$('#fc_add_upload_field').click( function()
	{
		add_new_upload_field( $('.fc_upload_fields:last') );
	});

	// Make names fit to the list
	$('.fc_name_short p').smartTruncation({ 'truncateCenter' : true });

	// Activate click-events to the list items
	set_media_buttons($('body'));
	set_media_functions();

	// Define buttons on the upper right site
	$('.fc_create_new_folder').unbind().click(function()
	{
		var current_active			= get_active_media(),
			folder_path				= current_active.find('input[name=folder_path]').val(), // Find the folderpath of chosen directory
			link					= ADMIN_URL + '/media/create_folder.php', // Create link for ajax
			dates					= 'folder_path=' + folder_path,
			beforeSend				= function ()
										{
											// Add fc_loader to the current folder
											current_active.prepend('<li class="fc_loader" />');
										},
			afterSend				= function ()
										{
											// Reload current folder to see if everthing really worked fine
											reload_folder( $(this) );
										};
		dialog_ajax( link, dates, beforeSend, afterSend, current_active, 'GET' );
	});

	// Activate the upload form to send data with ajax
	dialog_form(
		$('#fc_media_index_upload'), function()
		{
			var current_active		= get_active_media(),
				folder_path			= current_active.find('input[name=folder_path]').val(); 			// Find the folderpath of chosen directory

			$('#fc_media_index_upload input[name=folder_path]').val( folder_path );
		}, function()
		{
			reload_folder( get_active_media() );
			$('#fc_media_index_upload').find('input[type="reset"]').click(); 
		}
	);
});