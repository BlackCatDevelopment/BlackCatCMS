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
 * get the current ul.media_folder in which an element is active. If there is no active element the first folder is returned
 *
 **/
function get_active_media()
{
	if ( $('#fc_media_browser .fc_media_folder_active').size() > 0 )
	{
		return $('#fc_media_browser .fc_media_folder_active:first');
	}
	else if ( $('#fc_media_browser .fc_active').size() > 0 )
	{
		// Check if the activated item is not a folder to choose the current directory
		if ( $('#fc_media_browser .fc_active:first').hasClass('fc_filetype_file') )
		{
			return  $('#fc_media_browser .fc_active:first').closest('ul.fc_media_folder');
		}
		// If the activated type is a folder choose the next directory
		else
		{
			return $('#fc_media_browser .fc_active:first').closest('ul.fc_media_folder').next('ul.fc_media_folder');
		}
	}
	// If no file was activated choose the root directory
	else
	{
		$('ul.fc_media_folder:first').addClass('fc_media_folder_active');
		return $('ul.fc_media_folder:first');
	}
}

/**
 * send an ajaxRequest to media/ajax_rename.php to save the new name of a folder or file
 *
 * @param  object  rename_input - the input element where user edited the name
 * @param  string  extension - the previous fileextension
 *
 **/
function save_name( rename_input, extension )
{
	if ( typeof extension == 'undefined' ) { var extension = ''; }

	// Create link for ajax
	var current_active	= rename_input.parent('li'),
		current_ul		= current_active.closest('ul.fc_media_folder'),
		extension		= ( current_active.hasClass('fc_filetype_folder') || typeof extension == 'undefined' ) ? '' : extension;
		dates			= {
			'file_path':		current_ul.children('input[name=folder_path]').val(),
			'rename_file':		current_active.children('input[name=load_url]').val(),
			'new_name':			rename_input.val(),
			'extension':		extension,
            '_cat_ajax':        1
		};
	$.ajax(
	{
		type:		'POST',
		context:	current_active,
		url:		CAT_ADMIN_URL + '/media/ajax_rename.php',
		dataType:	'json',
		data:		dates,
		cache:		false,
		beforeSend:	function( data )
		{
			data.process	= set_activity( 'Save name' );
			rename_input.addClass('fc_name_update_process');
			$('#fc_media_info').hide();
			current_ul.prepend('<li class="fc_loader" />');
		},
		success:	function( data, textStatus, jqXHR  )
		{
			var current_active	= $(this),
				current_ul		= current_active.closest('ul.fc_media_folder'),
				rename_input	= current_active.children('input[name=rename]');

			current_ul.children('.fc_loader').remove();

			if ( data.success === true )
			{
				return_success( jqXHR.process , data.message);
				current_ul.addClass('fc_media_folder_active');
				current_ul.children('li').remove();
				reload_folder( current_ul );
			}
			else {
				return_error( jqXHR.process , data.message);
				rename_input.focus();
			}
			// Scroll to the very right to put the new loaded files into viewable area
			scrollToRight();
		}
	});
}

/**
 * send an ajaxRequest to media/ajax_get_contents.php to get all files from the given folder
 *
 * @param  object  current_ul - the input element where user edited the name
 * @param  string  folder_path - path to the current folder starting with MEDIA_DIRECTORY
 * @param  string  load_url - current folder or filename
 *
 **/
function reload_folder( current_ul, folder_path, load_url )
{
	// Create object for ajax
	var dates	= {
		'load_url':		typeof load_url !== 'undefined' ? load_url : '/',
		'folder_path':	typeof folder_path !== 'undefined' ? folder_path : current_ul.children('input[name=folder_path]').val(),
        '_cat_ajax':    1
	};

	$.ajax(
	{
		type:		'POST',
		context:	current_ul,
		url:		CAT_ADMIN_URL + '/media/ajax_get_contents.php',
		dataType:	'json',
		data:		dates,
		cache:		false,
		beforeSend:	function( data )
		{
			$('#fc_media_info').hide();
			current_ul.nextAll('ul').remove();
			current_ul.prepend('<li class="fc_loader" />');
		},
		success:	function( data, textStatus, jqXHR )
		{
			var current_ul	= $(this);
			current_ul.children('.fc_loader').remove();
			if ( data.is_folder )
			{
				//var current_ul	= $('ul.fc_media_folder:last');
				current_ul.append('<input type="hidden" name="folder_path" value="' + data.initial_folder + '">');
                $('#fc_media_index_upload input[name=folder_path]').val( data.initial_folder );
				
				if( typeof data.folders == 'undefined' && typeof data.files == 'undefined' )
				{
					$('ul.fc_media_folder_active').append('<li class="icon-info"> ' + cattranslate('No files available') + '</li>');
				}
				if( typeof data.folders != 'undefined' )
				{
					$.each(data.folders, function(index, value)
					{
						var insert	= '<li class="fc_filetype_folder" title="' + value.name + '"><div class="fc_name_short"><p class="icon-folder"> ' + value.name + '</p></div><input type="hidden" name="load_url" value="' + encodeURIComponent(value.name) + '" /></li>';
						$('ul.fc_media_folder_active').append(insert);
					});
				}
				if( typeof data.files != 'undefined' )
				{
					$.each(data.files, function(index, value)
					{
						var insert	= '<li class="fc_filetype_file" title="' + value.full_name + '"><div class="fc_name_short"><p class="icon-file-' + value.filetype  + '"> ' + value.full_name + '</p></div><input type="hidden" name="load_url" value="' + value.full_name + '" /></li>';
						$('ul.fc_media_folder_active').append(insert);
					});
				}
				current_ul.find('.fc_name_short p').smartTruncation({ 'truncateCenter' : true });
				// Activate all click-events to the new added list
				set_media_functions( current_ul );
				if ( !data.is_writable )
				{
					$('#fc_media_upload_not_writable').removeClass('hidden');
					$('#fc_media_index_upload').addClass('hidden');
				}
				else {
					$('#fc_media_index_upload').removeClass('hidden');
					$('#fc_media_upload_not_writable').addClass('hidden');
				}
			}
			else
			{
				// Get count of currently shown media-folders get the right position for media-info
				var mediaInfo	= $('#fc_media_info'),
					fileData	= data.files;

				mediaInfo.fadeIn(300);
				if ( fileData.show_preview )
				{
					$('.fc_file_info').children('img').remove();
					$('.fc_file_info').children('p').hide();
					$('.fc_file_info').prepend('<img src="' + fileData.load_url + '" alt="' + cattranslate('Preview') + '" />');
				}
				else
				{
					$('.fc_file_info').children('p').show();
					$('.fc_file_info').children('img').remove();
				}
				mediaInfo.find('.fc_filename').text(' '  + fileData.filename).removeClass().addClass('fc_filename icon-file-' + fileData.filetype);
				mediaInfo.find('.fc_file_type').text(fileData.filetype);
				mediaInfo.find('.fc_file_size').text(fileData.filesize);
				mediaInfo.find('.fc_file_date').text(fileData.filedate);
				mediaInfo.find('.fc_file_time').text(fileData.filetime);
			}
			// Scroll to the very right to put the new loaded files into viewable area
			scrollToRight();
		}
	});
}


/**
 * automatically scroll to the very right if an item was clicked
 *
 **/
function scrollToRight()
{
	var browser_width = $('#fc_media_browser').width();

	$('#fc_media_browser').animate({'scrollLeft': browser_width}, 0);

}


/**
 * (re)activate different events 'click' to set the active directoy and get optionally information about file/ contents of a given folder
 *
 * @param  object  element - the parent ul to bind all subelements
 *
 **/
function set_media_functions( element )
{
	// Prevent that when clicking an li the ul gets automatically the active element by toggleClass fc_clickable
	element.children('li').mouseover( function()
	{
		element.removeClass('fc_clickable');
	}).mouseout( function()
	{
		element.addClass('fc_clickable');
	});
	// If the given element is clicked and it hasClass fc_clickable this gets the active folder
	element.click( function()
	{
		var current	= $(this);
		if ( current.hasClass('fc_clickable') )
		{
			$('ul.fc_media_folder_active').removeClass('fc_media_folder_active');
			current.addClass('fc_media_folder_active');
		}
	});

	// Set click for all "non-folder"-items to set them active and show info
	element.children('.fc_filetype_file, .fc_filetype_folder').not('.fc_no_content, .fc_save_rename').unbind('click').click( function()
	{
		// Store different values for later use
		var current		= $(this),
			current_ul	= current.closest('ul.fc_media_folder'),
			load_url	= current.children('input[name=load_url]').val(),
			folder_path	= current_ul.children('input[name=folder_path]').val();
		// Remove active-Class from all other list-items
		$('ul.fc_media_folder_active').removeClass('fc_media_folder_active');

		// change all previous folders from fc_active to fc_open_folder
		current_ul.prevAll('ul.fc_media_folder').find('.fc_active').addClass('fc_open_folder').removeClass('fc_active');

		// remove all folders after current folder
		current_ul.nextAll('ul.fc_media_folder').remove();

		// remove classes fc_active and fc_open_folder in current folder and set current element to fc_active
		current_ul.children('.fc_active').removeClass('fc_active');
		current_ul.children('.fc_open_folder').removeClass('fc_open_folder');
		current.addClass('fc_active');

		// Get the left distance to place the new mediafolder/ media_info
		var leftValue	= $('ul.fc_media_folder').size() * 250;
		if ( current.hasClass('fc_filetype_folder') )
		{
			current_ul	= $('<ul class="fc_media_folder fc_gradient1 fc_media_folder_active fc_clickable" />').insertAfter(current_ul).css({ left: leftValue });
		}
		else
		{
			current_ul.addClass('fc_media_folder_active');
			$('#fc_media_info').css({ left: leftValue });
		}

		// reload all contents in the current folder
		reload_folder( current_ul, folder_path, load_url );
	});
}


/**
 * (re)activate different events 'click' to for the delete and rename-buttons
 *
 * @param  object  upload_field - the parent ul to bind all subelements .fc_upload_close
 *
 **/
function copy_upload_field( upload_field )
{
	var current_field		= upload_field.children('input[type=file]');

	upload_field.find('.fc_upload_close').click( function()
	{
		upload_field.closest('.fc_upload_fields').remove();
		if( $('#fc_media_index_upload').find('input[type=file]').size() > 1 )
		{
			// Remove field if there are still more than one left
			$('.fc_upload_field:last').removeClass('fc_inactive');
		}
        else
		{
			add_new_upload_field();
			$('.fc_upload_field:first').addClass('fc_inactive');
		}
	});

	current_field.change( function()
	{
		// get all values needed later
		var file			= current_field.val(),
			filename		= file.substr( (file.lastIndexOf('\\') + 1) ),
			extension		= file.substr( (file.lastIndexOf('.') + 1) ).toLowerCase(),
			allowed_ext		= current_field.prop('accept').split('\|');

		if ( jQuery.inArray( extension, allowed_ext ) > -1 )
		{
			upload_field.children('input[type=text]').val( filename );
			if ( !upload_field.hasClass('fc_inactive') )
			{
				add_new_upload_field ( );
				upload_field.addClass('fc_inactive');
			}
		
			// Check extesion and bind some effects to different kinds
			switch(extension)
			{
				case 'zip':
				case 'rar':
				case 'gz':
					upload_field.next('.fc_upload_zip').removeClass('hidden');
				break;
				default:
					upload_field.next('.fc_upload_zip').addClass('hidden');
				break;
			}
		}
		else {
			upload_field.switchClass( 'fc_gradient4', 'fc_gradient_red', 300 );
			setTimeout(function(){
				upload_field.switchClass( 'fc_gradient_red', 'fc_gradient4', 3000 );
			}, 1200);
		}
	});
}


/**
 * (re)activate different events 'click' to for the delete and rename-buttons
 *
 * @param  object  element - the parent ul to bind all subelements
 *
 **/
function add_new_upload_field ()
{
	var field_size		= parseInt( $('#fc_media_index_upload .fc_upload_fields:last').children('input[name^=upload_counter]').val(), 10 ) + 1,
		new_field		= $('#fc_upload_field_add > div').clone().removeClass('hidden').insertAfter('#fc_media_index_upload .fc_upload_fields:last');

	new_field.children('input[name^=upload_counter]').val( field_size );
	new_field.find( 'input[type=file]' ).prop( 'name', 'upload_' + field_size );

	new_field.find( 'input[name=unzip_]' ).prop( 'name', 'unzip_' + field_size ).prop( 'id', 'unzip_' + field_size ).addClass( 'show___fc_delete_zip_div_' + field_size ).next('label').prop( 'for', 'unzip_' + field_size );
	new_field.find( 'input[name=delete_zip_]' ).prop( 'name', 'delete_zip_' + field_size ).prop( 'id', 'delete_zip_' + field_size ).next('label').prop( 'for', 'delete_zip_' + field_size ).closest('div').prop( 'id', 'fc_delete_zip_div_' + field_size );

	// Set function for new added field
	new_field.find( '.fc_toggle_element' ).fc_toggle_element();
	copy_upload_field( new_field.children('.fc_upload_field') );
}


jQuery(document).ready(function()
{
	$('#fc_upload_button').click(function()
	{
		$('.fc_upload_field').remove();
		add_new_upload_field( false );
	});

	copy_upload_field( $('#fc_media_index_upload .fc_upload_field:first') );

	$('#fc_add_upload_field').click( function()
	{
		add_new_upload_field( $('.fc_upload_field:last') );
	});
	$('#fc_header_button_dropdown_toggle a').click( function(e)
	{
		e.preventDefault();
		$('#fc_media_index_upload_ul').toggle(300);
		$(this).toggleClass('fc_active', 300);
	});

	$('#fc_close_media').click( function()
	{
		var media_upload	= $('#fc_media_index_upload');
		media_upload.children('.fc_upload_fields:first').nextAll('.fc_upload_fields').remove();
		media_upload.find('.fc_upload_field').removeClass('fc_inactive');
		media_upload.find( '.fc_toggle_element' ).prop( 'checked', false ).click();
		media_upload.find('.fc_upload_zip').addClass('hidden');
		$('#fc_header_button_dropdown_toggle a').click();
	});
	
	// Make names fit to the list
	$('.fc_name_short p').smartTruncation({ 'truncateCenter' : true });

	// Activate click-events to the list items
	set_media_functions( $('ul.fc_media_folder') );
	
	/**
	 * activate different events 'click' to for the delete and rename-buttons
	 *
	 *
	 **/
	$('.fc_delete_file').unbind().click( function(e)
	{
		e.preventDefault();
		// Check if any item is active
		if ( $('#fc_media_browser li.fc_active').size() === 0 )
		{
			// if not deactive the button
			$(this).addClass('fc_inactive_button');
		}
		else
		{
			// Store current active item in a variable
			var current_active	= $('#fc_media_browser li.fc_active');
			// Get the previous name of the file that is supposed to be deleted (files and folders has to be seperated!)
			if ( current_active.hasClass('fc_filetype_file') )
			{
				var name		= '<br/><strong>'+current_active.find('.fc_name_short').text()+'</strong>',
					message		= cattranslate('Are you sure you want to delete file {name}').replace( /\{name\}/g, name ),
					type		= 'file';
			}
			else
			{
				var name		= '<br/><strong>' + current_active.find('.fc_name_short').text() + '</strong>',
					message		= cattranslate('Are you sure you want to delete the directory {name}').replace( /\{name\}/g, name ),
					type		= 'folder';
			}
			// Create dates for ajax
			var dates			= {
									'load_url':		current_active.children('input[name=load_url]').val(),
									'file_path':	current_active.closest('ul.fc_media_folder').find('input[name=folder_path]').val(),
									'file':			current_active.find('input[name=load_url]').val(),
									'type':			type,
                                    '_cat_ajax':    1
								},
				beforeSend		= function( data )
				{
					$('#fc_media_browser li.fc_active').closest('ul').prepend('<li class="fc_loader" />');
					$('#fc_media_info').hide();
				},
				afterSend		= function( data, textStatus, jqXHR )
				{
					$('li.fc_loader').remove();
					if ( data.success === true )
					{
						var current		= $(this),
							current_ul	= current.closest('ul'),
                            parent_id   = current.parent().parent().prop('id');
						if ( current_ul.children('li').size() == 1 )
						{
                            if(parent_id != 'fc_media_browser')
                            {
							current_ul.nextAll('ul.fc_media_folder').andSelf().remove();
                            }
                            else
                            {
                                current_ul.nextAll('ul.fc_media_folder').remove();
                                current_ul.html(
                                    '<ul class="fc_media_folder fc_media_folder_active fc_clickable">' +
                            		'    <input type="hidden" value="' + current_ul.find('input[name="folder_path"]').val() + '" name="folder_path">' +
     						        '    <li class="fc_filetype_file fc_no_content">' + cattranslate('No files available') + '</li>' +
                            		'</ul>'
                                );
                            }
							var fc_active	= $('.fc_open_folder:last');
							fc_active.closest('ul').removeClass('fc_clickable');
							fc_active.click();
						}
						else if ( current.hasClass('fc_filetype_folder') )
						{
							current_ul.nextAll('ul.fc_media_folder').remove();
							current_ul.click();
						}
						else
						{
							current_ul.nextAll('ul.fc_media_folder').remove();
							current_ul.click();
						}
						current.remove();
					}
					else
					{
						return_error( jqXHR.process , data.message);
					}
				};
			
			dialog_confirm( cattranslate('Do you really want to delete this ' + type + '?'), cattranslate('Delete ' + type), CAT_ADMIN_URL + '/media/ajax_delete.php', dates, 'POST', 'JSON', beforeSend, afterSend, current_active );
		}
	});

	$('.fc_rename_file').unbind().click( function()
	{
		// Store current active item in a variable
		var current_active = $('#fc_media_browser .fc_active');

		// Get the previous name of the file that is supposed to be renamed (files and folders has to be seperated!)
		var get_name	= current_active.children('input[name=load_url]').val(),
			old_name	= current_active.hasClass('fc_filetype_folder') ? get_name : get_name.substr( 0, (get_name.lastIndexOf('.') ) ),
			extension	= current_active.hasClass('fc_filetype_folder') ? '' : get_name.substr( (get_name.lastIndexOf('.') + 1) );

		// add the input field to the list item that should be renamed
		var rename_input	= $('<input type="text" name="rename" value="' + old_name + '" />').appendTo( current_active ).slideUp(0).slideDown(200).focus();
		var save_input		= $('<span class="icon-checkmark fc_gradient1 fc_gradient_hover fc_border fc_save_rename" />').appendTo( current_active ).click( function(e)
		{
			rename_input.addClass('fc_name_update_process');
			save_name( rename_input, extension );
		});
		current_active.children('.fc_name_short').slideUp(0);

		// Activate the keydown event to save the name if "enter" is pressed
		rename_input.keydown(function(event)
		{
			if ( event.keyCode == '13' )
			{
				save_name( rename_input, extension );
			}
		});

		// Bind the mouseover event to the save-button to add a class so the script can check on focusout whether the name shall be saved or not
		save_input.mouseenter( function()
		{
			// Tell the script that the name is currently updated
			rename_input.addClass('fc_name_update_process');
		}).mouseleave( function()
		{
			// Tell the script that the name is not currently updated
			rename_input.removeClass('fc_name_update_process');
		});
		// Bind focus out to the input-field
		rename_input.focusout( function()
		{
			// Set timeout so the clickevent on .save_rename works
			if ( !rename_input.hasClass('fc_name_update_process') )
			{
				// show name again and remove the input field
				save_name( rename_input, extension );
				// Activate the click events for list items again
				//set_media_functions(  );
			}
		});
	});

	$('.fc_create_new_folder').unbind().click(function() {
        // bind ENTER to dialog
        $(document).delegate('.ui-dialog', 'keyup', function(e) {
            var tagName = e.target.tagName.toLowerCase();
            tagName = (tagName === 'input' && e.target.type === 'button') ? 'button' : tagName;
            if (e.which === $.ui.keyCode.ENTER && tagName !== 'textarea' && tagName !== 'select' && tagName !== 'button') {
                $(this).find('.ui-dialog-buttonset button').eq(0).trigger('click');
                return false;
            }
        });
        var $dialog = $('<div><input type="text" name="fc_media_add_folder_name" style="width:100%" value="" /></div>')
			.dialog({
				autoOpen: false,
				width: 300,
				height: 180,
                title: cattranslate('Folder name'),
                buttons: [
                    {
                        text: cattranslate('Save'),
                        click: function() {
                            $(this).dialog("close");
		// Create link for ajax
		var current_ul		= get_active_media(),
			dates			= {
								'folder_path':	current_ul.children('input[name=folder_path]').val(),
                                                    'name'       : $(this).find('input:first').val(),
                                '_cat_ajax':    1
							};

		$.ajax(
		{
			type:		'POST',
			context:	current_ul,
			url:		CAT_ADMIN_URL + '/media/ajax_create_folder.php',
			dataType:	'json',
			data:		dates,
			cache:		false,
			beforeSend:	function( data )
			{
				data.process	= set_activity('Create folder');
				$('#fc_media_info').hide();
				current_ul.children('li').remove().prepend('<li class="fc_loader" />');
			},
			success:	function( data, textStatus, jqXHR  )
			{
				current_ul.children('.fc_loader').remove();

				if ( data.created === true )
				{
					return_success( jqXHR.process , data.message);
					reload_folder( current_ul );
				}
				else
				{
					return_error( jqXHR.process , data.message);
				}
			}
		});
                        }
                    },
                    {
                        text: cattranslate('Cancel'),
                        click: function() { $(this).dialog("close"); }
                    },
                ]
 			});
        $dialog.dialog('open');
    	return false;
	});

	// Activate the upload form to send data with ajax
	dialog_form(
		$('#fc_media_index_upload'), function( data )
		{
			var current_ul		= get_active_media(),
				folder_path		= current_ul.find('input[name=folder_path]').val();

			$('#fc_media_index_upload input[name=folder_path]').val( folder_path );
		}, function( data, textStatus, jqXHR )
		{
			var media_upload	= $('#fc_media_index_upload'),
				reload_ul		= get_active_media();

			reload_ul.children('li').remove();

			reload_folder( reload_ul );

			media_upload.children('.fc_upload_fields:first').nextAll('.fc_upload_fields').remove();
			media_upload.find('.fc_upload_field').removeClass('fc_inactive');
			media_upload.find('.fc_toggle_element').prop( 'checked', false ).click();
			media_upload.find('.fc_upload_zip').addClass('hidden');

			$('#fc_media_index_upload').find('input[type="reset"]').click();
		}
	);
});