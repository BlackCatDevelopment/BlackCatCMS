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

// jQuery-Plugin to implement not case-sensitive function :contains
$.extend($.expr[':'], {
	'containsi': function(elem, i, match, array)
	{
		return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
	}
});

// Plugin to validate email
function isValidEmailAddress(emailAddress) {
var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
return pattern.test(emailAddress);
}




// ===================== 
// ! BACKEND FUNCTIONS   
// ===================== 

// Function for Ajax to get the lepToken
function getToken()
{
	return $('#fc_hidden_inputs input[name=leptoken]').val();
}

function getThemeName()
{
	return 'freshcat';
}

// Function to show the dialog with error message if an ajax reports an error
function return_error( data, process_div )
{
	$('.fc_loader').remove();
	if ( process_div != false )
	{
		// Remove previously generated .process if an error occured
		process_div.slideUp(1200,function()
		{
			process_div.remove();
		});
	}

	// Find the error message inside the returned data
	var content		= $( data );
	var message		= content.find('.fc_error_box').html();

	// Check if .popup exists - if not add div.popup before #admin_header
	if ( $('.popup').size()==0 )
	{
		$('#fc_admin_header').prepend('<div class="popup" />');
	}

	// add error message to popup
	$('.popup').html(message);

	// Remove JS-Fallback from success message and backlink
	$('.popup').find('.fc_fallback').remove();

	// get title for dialog
	title = set_popup_title();

	// Activate dialog on popup
	$('.popup').dialog(
	{
		create: function(event, ui)
		{
			// Only some cosmetical style changes ;-)
			$('.ui-widget-header').removeClass('ui-corner-all').addClass('ui-corner-top');
		},
		// Only some cosmetical style changes (adding shadow);-)
		dialogClass:	'ui-widget-shadow',
		modal:			true,
		show:			'fade',
		closeOnEscape:	true,
		title:			title,
		// Add a ok-button to submit the popup
		buttons: [
			{
				'text':		'Ok',
				'click':	function()
				{
					$('.popup').dialog('destroy'); 
				},
				'class':	'submit'
			}
		]
	});
}

// Function to display the success of an ajax submit in the #activity -- process_div is the div.process you got before from set_activity();
function return_success(data,process_div)
{
	// Find the error message inside the returned data
	var message		= $( data ).find('.fc_success_box').html();
	if ( message == '' ) return;

	// Check if .popup exists - if not add div.popup before #admin_header
	if ( $('.popup').size() == 0 )
	{
		$('#fc_admin_header').prepend('<div class="popup" />');
	}

	// add success message to popup to use function set_popup_title();
	$('.popup').html(message);

	// Remove JS-Fallback from success message and backlink
	$('.popup').find('.fc_fallback').remove();

	// Get complete message
	var message		= $('.popup').html();

	if ( typeof process_div != 'undefined' )
	{
		process_div.html(message);

		// Show success message for 5000 ms, slide it up and remove it from #activity
		setTimeout(function()
		{
			process_div.slideUp(1200,function()
			{
				process_div.remove();
			});
		},5000);
	}
}

// Function to show activity in the sidebar
function set_activity( title )
{
	if ( typeof title == 'undefined')
	{
		var title	= 'Loading';
	}
	// Add a div.process to #activity and store in a variable to use it later
	var process		= $('<div class="fc_process" />').appendTo('#fc_activity');

	// initial hide the .process...
	process.slideUp(0,function()
	{
		// ...fill it with the title and a loader and show it
		process.html('<div class="fc_process_title">' + title + '</div><div class="loader" />').slideDown(300);
	});

	// Return the div.process to hide update it after successful ajax
	return process;
}

// Function to get the title of the popup - if not set "Message" will be added instead
function set_popup_title()
{
	// Set a default value
	var title		= LEPTON_TEXT['DEFAULT_MESSAGE_TITLE'];

	// Check if the .popup has a .popup_header
	if ( $('.popup .popup_header').size() > 0 )
	{
		// Get the content (text) of the .popup_header and set title
		var title	= $('.popup .popup_header').text();

		// Remove Popup
		$('.popup .popup_header').remove();
	}
	return title;
}


// Function to show a confirm popup to confirm clicks, like delete page, user, groups etc.
// you can optionally define a function that is called before (beforeSend) ajaxRequest and one that is called after (afterSend)
function dialog_confirm( message, link, beforeSend, afterSend, jQcontext )
{
	// Check if .popup exists - if not add div.popup before #admin_header
	if ( $('.popup').size()==0 )
	{
		$('#fc_admin_header').prepend('<div class="popup" />');
	}

	if ( typeof jQcontext == 'undefined' )
	{
		jQcontext = 'document.body';
	}

	link	= link + '&leptoken=' + getToken();

	// Add message to .popup to use function set_popup_title();
	$('.popup').html(message);

	// Get title for dialog
	title = set_popup_title();

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
					context:	jQcontext,
					url:		link,
					dataType:	'html',
					beforeSend:	function( data )
					{
						// Add a .process to #activity bar to show user, the data is send to server
						process_div		= set_activity(title);
						// Hide .popup
						$('.popup').dialog('destroy');
						// check if a function beforeSend is defined and call it if true
						if ( typeof beforeSend != 'undefined' && beforeSend != false )
						{
							beforeSend.call(this);
						}
					},
					success:	function( data )
					{
						// Check if there is a div.success_box in returned data that implements that the request was completely successful
						if ( $( data ).find('.fc_success_box').size() > 0 )
						{
							// Return success message --- process_div is the previously generated .process inside #activity
							if ( typeof process_div != 'undefined' )
							{
								return_success( data, process_div );
							}
							// check if a function afterSend is defined and call it if true
							if ( typeof afterSend != 'undefined' && afterSend != false )
							{
								afterSend.call(this, data);
							}
						}
						else if ( $( data ).find('.fc_error_box').size() > 0 )
						{
							// return error
							return_error( data, process_div );
						}
						else return;
					},
					error:		function( data )
					{
						return_error( data, process_div );
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
				$('.popup').dialog('destroy');
			},
		'class':	'reset'
	});

	// acitvate dialog on popup
	$('.popup').dialog(
	{
		create: function(event, ui)
		{
			// Only some cosmetical style changes ;-)
			$('.ui-widget-header').removeClass('ui-corner-all').addClass('ui-corner-top');
		},
		// Only some cosmetical style changes
		dialogClass:	'ui-widget-shadow',
		modal:			true,
		show:			'fade',
		closeOnEscape:	true,
		title:			title,
		buttons:		buttonsOpts
	});
}

// Function to simply send an ajaxRequest with option to call functions before and after sending data
function dialog_ajax( link, dates, beforeSend, afterSend, jQcontext, type )
{
	if ( typeof jQcontext == 'undefined' )
	{
		jQcontext = 'document.body';
	}
	if ( typeof type == 'undefined' )
	{
		type = 'GET';
	}
	dates			= dates + '&leptoken=' + getToken();
	var req			= $.ajax(
	{
		type:		type,
		url:		link,
		dataType:	'html',
		context:	jQcontext,
		data:		dates,
		beforeSend:	function( data )
		{
			// deactive .popup before send data
			$('.popup').dialog('destroy');
			// check if a function beforeSend is defined and call it if true
			if ( typeof beforeSend != 'undefined' && beforeSend != false )
			{
				beforeSend.call(this);
			}
		},
		success:	function( data )
		{
			// Check if there is a div.success_box in returned data that implements that the request was completely successful
			if( $( data ).find('.fc_success_box').size() > 0 )
			{
				// check if a function afterSend is defined and call it if true
				if ( typeof afterSend != 'undefined' && afterSend != false )
				{
					afterSend.call(this, data);
				}
			}
			else if ( $( data ).find('.fc_error_box').size() > 0 )
			{
				if ( typeof process_div == 'undefined' )
				{
					var process_div = false;
				}
				// return error
				return_error( data, process_div );
			}
			else return;
		},
		error:		function( data )
		{
			if ( typeof process_div != 'undefined' )
			{
				var process_div = false;
			}
			return_error( data, process_div );
		}
	}).success( function( data )
	{
		// Check if there is a div.success_box in returned data that implements that the request was completely successful
		if( $( data ).find('.fc_success_box').size() > 0 )
		{
			// Return success message --- process_div is the previously generated .process inside #activity
			if ( typeof process_div != 'undefined' )
			{
				return_success( data, process_div );
			}
		}
	});
}

// Function to define confirm of forms showing in a dialog and adding a optional beforeSend and afterSend
function dialog_form( currentForm, beforeSend, afterSend )
{
	// If form is submitted
	currentForm.submit(function()
	{
		// Define ajax for form
		currentForm.ajaxSubmit(
		{
			clearForm:		true,
			beforeSend:		function( data )
			{
				// Check if the form has a (mostly hidden) input field with a title for the form (if not 'loading' is used
				if ( currentForm.find('input[name=form_title]').size() > 0 )
				{
					var title	= currentForm.find('input[name=form_title]').val();
				}
				else {
					var title = 'Loading';
				}

				// Set activity and store in a variable to use it later
				process_div		= set_activity(title);

				// Destroy dialog to hide the 
				currentForm.dialog('destroy');

				// check if a function beforeSend is defined and call it if true
				if ( typeof beforeSend != 'undefined' && beforeSend != false )
				{
					beforeSend.call(this);
				}
			},
			success:		function( data )
			{
				// Check if there is a div.success_box in returned data that implements that the request was completely successful
				if( $( data ).find('.fc_success_box').size() > 0 )
				{
					// Return success message --- process_div is the previously generated .process inside #activity
					return_success( data, process_div );

					// check if a function afterSend is defined and call it if true
					if ( typeof afterSend != 'undefined' && afterSend != false )
					{
						afterSend.call(this, data);
					}
				}
				else {
					// else return error
					return_error(data,process_div);
				}
			},
			error:		function( data )
			{
				return_error(data,process_div);
			}
		});

		// Prevent form from being send twice!
		return false;
	});
}


// Function to activate buttons (for example if a some new elements where added to the body with ajax)
function set_buttons( element )
{
	// Add ids to select, to fix a bug of selectmenu, if there is no id set to the label
	$( element ).find('select').each( function()
	{
		var current_select		= $(this);
		if ( typeof current_select.attr('id') == 'undefined' )
		{
			current_select.attr( 'id', current_select.attr('name') );
		}
	});

	$( element ).find('input.fc_checkbox_jq').each( function()
	{
		var current_select		= $(this);
		if ( typeof current_select.attr('id') == 'undefined' )
		{
			current_select.attr( 'id', current_select.attr('name') );
		}
	});

	// Activate jQuery selectmenu
	$( element ).find('select').selectmenu(
	{
		style:		'popup',
		width:		200,
		icons:		{
			primary:			"ui-icon-carat-2-n-s"
		}
	});

	// Activate jQuery UI Tabs, if there are tabs
	if ( $( element ).find('#fc_tabs').size() > 0 )
	{
		$( element ).find('#fc_tabs').tabs();
	}
	if ( $( element ).find('.fc_tabs').size() > 0 )
	{
		$( element ).find('.fc_tabs').tabs();
	}

	// Activate jQuery UI Buttons for radio-buttons
	$( element ).find('input.fc_radio_jq').each( function()
	{
		// Get the id of each element
		var input_id	= $(this).attr('id');

		// Check if the input has label
		// This needs to be done as there are some coder that don't know the importance of semantic html ;-)
		if ( $('label[for=' + input_id + ']').size() > 0 )
		{
			$(this).button(
			{
				icons: {
					secondary:		"ui-icon-radio"
				}
			});
		}
	});

	// Activate jQuery UI Buttons for checkboxes
	$( element ).find('input.fc_checkbox_jq').each( function()
	{
		// Get the id of each element
		var input_id	= $(this).attr('id');

		// Check if the input has label
		// This needs to be done as there are some coder that don't know the importance of semantic html ;-)
		if ( $('label[for=' + input_id + ']').size() > 0 )
		{
			$(this).button(
			{
				icons: {
					secondary:		"ui-icon-on-off"
				}
			});
		}
	});

	// Activate toggle for select
	element.find( '.fc_toggle_element' ).fc_toggle_element();
}

function searchUsers( searchTerm )
{
	$('#fc_list_overview li').removeClass('fc_activeSearch').slideDown(0);
	if ( searchTerm.length > 0 )
	{
		$('#fc_list_overview li:containsi(' + searchTerm + ')').addClass('fc_activeSearch');
		if ( $('#fc_list_overview').hasClass('fc_settings_list') )
		{
			$('.fc_form_content:containsi(' + searchTerm + ')').each( function()
			{
				var id	= $(this).attr('id');
				$('#fc_list_overview li[rel=' + id + ']').addClass('fc_activeSearch');
			});
		}
		$('#fc_list_overview li').not('.fc_activeSearch').slideUp(300);
	}
	else
	{
		$('#fc_list_overview li').slideDown(300);
	}

}

// Marked as deprecated!

function confirm_link ( message, link )
{
	var afterSend		= function()
	{
		location.reload(true);
	}
	dialog_confirm( message, link, false, afterSend, false );

}


jQuery(document).ready( function()
{
	// Activate tagit for Keywords in the adding
	$('#fc_addPage_keywords_ul').tagit(
	{
		allowSpaces:		true,
		singleField:		true,
		singleFieldNode:	$('#fc_addPage_keywords')
	});

	// Check if a cookie for sidebar is defined
	if ( typeof $.cookie('sidebar') != 'undefined' )
	{
		// Get current width of browser and cookie for width of the sidebar
		var window_width = parseInt( $(window).width() ),
			width	= $.cookie('sidebar');
		// Some resizes of elements
		$('#fc_content_container, #fc_content_footer').css(
		{
			width:		( window_width - width ) + 'px'
		});
		$('#fc_sidebar_footer, #fc_sidebar, #fc_activity, #fc_sidebar_content').css(
		{
			width:		width + 'px'
		});
	}

	// Add resize to window
	$(window).resize_elements();

	// Make the sidebar resizeable
	$('#fc_sidebar_footer, #fc_sidebar').resizable(
	{
		handles: 'e',
		minWidth: 100,

		start: function(event, ui)
		{
			// store width of browser
			window_width = parseInt( $(window).width() );
		},
		resize: function(event, ui)
		{
			// resize also some elements
			$('#fc_content_container, #fc_content_footer').css({width: ( window_width - ui.size.width )+'px'});
			$('#fc_sidebar_footer, #fc_sidebar, #fc_activity, #fc_sidebar_content').css({width: ui.size.width+'px'});
		},
		stop: function(event, ui)
		{
			// Save new width of sidebar in a cookie
			$.cookie('sidebar', ui.size.width, {path: '/'});
		}
	});

	// Initial activation of click events
	set_buttons($('body'));

	// Bind reset button with timeout reset, to reset even jQuery UI-Buttons and Dropdowns
	$('input:reset').click(function()
	{
		setTimeout( function()
		{
			$('.fc_advanced_groups input').change();
		}, 10 );
	});

	$('#fc_list_search input').livesearch(
	{
		searchCallback:			searchUsers,
		queryDelay:				250,
		innerText:				LEPTON_TEXT['SEARCH'],
		minimumSearchLength:	2
	});

	$('.fc_input_fake label').click( function()
	{
		var input	= $(this).attr('for');
		$('#' + input).val('');
		searchUsers('');
	});

	// Activate automatically sending forms with ajax, if they have the class ".ajaxForm"
	$('.ajaxForm').each(function()
	{
		dialog_form( $(this) );
	});

	// Bind buttons to show popups
	$('.show_popup').fc_show_popup();
});