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
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         freshcat
 *
 **/

if ( typeof jQuery != 'undefined' )
{
    jQuery.ajaxSetup({
        error: function( jqXHR, textStatus, errorThrown )
        {
            console.log(textStatus);
            console.log(errorThrown);
            if (jqXHR.status === 0) {
                alert('Not connected.\n Verify Network.');
            } else if (jqXHR.status == 404) {
                alert('Requested page not found. [404]');
            } else if (jqXHR.status == 500) {
                alert('Internal Server Error [500].');
            } else if (errorThrown === 'parsererror') {
                alert('JSON parse failed.');
            } else if (errorThrown === 'timeout') {
                alert('Time out error.');
            } else if (errorThrown === 'abort') {
                alert('Ajax request aborted.');
            } else {
                if(jqXHR.responseText.indexOf('fc_login_form') != -1) {
                    location.href = CAT_ADMIN_URL + '/login/index.php';
                } else {
                    alert('Uncaught Error.\n' + jqXHR.responseText);
                }
            }
        }
    });
}

// Avoid `console` errors in browsers that lack a console.
// Source: https://github.com/h5bp/html5-boilerplate/blob/master/js/plugins.js
(function() {
    var method;
    var noop = function () {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());

$.expr[":"].containsi = $.expr.createPseudo(function (selector, context, isXml) {
    return function (elem) {
        return (elem.textContent || elem.innerText || $.text(elem)).toLowerCase().indexOf(selector.toLowerCase()) > -1;
    };
});


// Plugin to validate email
function isValidEmailAddress(emailAddress) {
	var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
	return pattern.test(emailAddress);
}


// =====================
// ! BACKEND FUNCTIONS
// =====================

function getThemeName()
{
	return 'freshcat';
}

function getDatesFromQuerystring(q)
{
    var vars = [], hash;
    if(q != undefined)
    {
        q = q.split('&');
        for(var i = 0; i < q.length; i++)
        {
            hash = q[i].split('=');
            vars.push(hash[1]);
            vars[hash[0]] = hash[1];
        }
    }
    return vars;
}

// match css class with given prefix
function match_class_prefix(prefix,elem)
{
	var classes = elem.prop('class').split(' ');
	var regex   = new RegExp('^'+prefix+'(.+)',"g");
	for (var i = 0; i < classes.length; i++)
	{
		var matches = regex.exec(classes[i]);
		if (matches !== null)
		{
			return matches[1];
		}
	}
}

function togglePageTree()
{
	if ( !$('#fc_sidebar').is(':visible') )
	{
		var new_width	= typeof $.cookie('sidebar') != 'undefined' ? $.cookie('sidebar') : 200;
		$('#fc_sidebar, #fc_sidebar_footer').css({width: new_width}).show();
	}
	else {
		$('#fc_sidebar, #fc_sidebar_footer').css({width: 0}).hide();
	}
	$(window).resize();
}

// Function to show the dialog with error message if an ajax reports an error
function return_error( process_div, message )
{
	// Remove previously generated .process if an error occured
	process_div.slideUp(1200,function()
	{
		process_div.remove();
	});

	// Check if .fc_popup exists - if not add div.fc_popup before #admin_header
	if ( $('.fc_popup').size() === 0 )
	{
		$('#fc_admin_header').prepend('<div class="fc_popup" />');
	}

	// add error message to popup
	$('.fc_popup').html(message);

	// get title for dialog
	var title = set_popup_title();

	// Activate dialog on popup
	$('.fc_popup').dialog(
	{
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
					$('.fc_popup').dialog('destroy');
				},
				'class':	'submit'
			}
		]
	});
}

// Function to display the success of an ajax submit in the #activity -- process_div is the div.process you got before from set_activity();
function return_success( process_div, message )
{
	if ( typeof message != 'undefined' )
	{
		process_div.html(message).addClass('fc_active');

		// Show success message for 5000 ms, slide it up and remove it from #activity
		setTimeout(function()
		{
			process_div.slideUp(1200,function()
			{
				process_div.remove();
			});
		},5000);
	}
	else {
		process_div.slideUp(1200,function()
		{
			process_div.remove();
		});
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
	var process		= $('<div class="fc_process fc_gradient1 fc_border" />').appendTo('#fc_activity');

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
	var title		= CAT_TEXT['DEFAULT_MESSAGE_TITLE'];

	// Check if the .fc_popup has a .fc_popup_header
	if ( $('.fc_popup .fc_popup_header').size() > 0 )
	{
		// Get the content (text) of the .fc_popup_header and set title
		var title	= $('.fc_popup .fc_popup_header').text();

		// Remove Popup
		$('.fc_popup .fc_popup_header').remove();
	}
	return title;
}

// Function to show a confirm popup to confirm clicks, like delete page, user, groups etc.
// you can optionally define a function that is called before (beforeSend) ajaxRequest and one that is called after (afterSend)
function dialog_confirm( message, title, ajaxUrl, ajaxData, ajaxType, ajaxDataType, beforeSend, afterSend, ajaxjQcontext )
{
	// Check if .fc_popup exists - if not add div.fc_popup before #admin_header
	if ( $('.fc_popup').size() === 0 )
	{
		$('#fc_admin_header').prepend('<div class="fc_popup" />');
	}

    // Check if cattranslate() is available
    if ( typeof cattranslate != 'undefined' )
    {
        message = cattranslate(message);
    }
	// Add message to .fc_popup to use function set_popup_title();
	$('.fc_popup').html( message );

	// check for all necessary values
	var ajaxUrl			= typeof ajaxUrl == 'undefined' || ajaxUrl === false					? alert( 'You sent an invalid url' ) : ajaxUrl,
		ajaxType		= typeof ajaxType == 'undefined' || ajaxType === false				    ? 'POST' : ajaxType,
		ajaxDataType	= typeof ajaxDataType == 'undefined' || ajaxDataType === false		    ? 'JSON' : ajaxDataType,
		ajaxjQcontext	= typeof ajaxjQcontext == 'undefined' || ajaxjQcontext === false		? $('document.body') : ajaxjQcontext,
		title			= typeof title == 'undefined' || title === false						? set_popup_title() : title;

	// Set the array for confirm-buttons
	buttonsOpts = new Array();

    ajaxData['_cat_ajax'] = 1;

	// define button for confirm dialog positive
	buttonsOpts.push(
	{
		'text':		cattranslate('YES'), 'click':  function()
			{
				$.ajax(
				{
					type:		ajaxType,
					context:	ajaxjQcontext,
					url:		ajaxUrl,
					dataType:	ajaxDataType,
					data:		ajaxData,
					cache:		false,
					beforeSend:	function( data )
					{
						// Set activity and store in a variable to use it later
						data.process	= set_activity( title );

						// Hide .fc_popup
						$('.fc_popup').dialog('destroy').remove();

						// check if a function beforeSend is defined and call it if true
						if ( typeof beforeSend != 'undefined' && beforeSend !== false )
						{
							beforeSend.call(this, data);
						}
					},
					success:	function( data, textStatus, jqXHR )
					{
						if ( data.success === true || $(data).find('.fc_success_box').size() > 0 )
						{
							// Check if there is a div.success_box in returned data that implements that the request was completely successful
							return_success( jqXHR.process , data.message );
							// check if a function afterSend is defined and call it if true
							if ( typeof afterSend != 'undefined' && afterSend !== false )
							{
								afterSend.call(this, data);
							}
						}
						else {
							// return error
							return_error( jqXHR.process , data.message );
						}
					},
					error:		function( data, textStatus, jqXHR )
					{
						return_error( jqXHR.process , data.message );
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
				$('.fc_popup').dialog('destroy');
			},
		'class':	'reset'
	});

	// acitvate dialog on popup
	$('.fc_popup').dialog(
	{
		modal:			true,
		show:			'fade',
		closeOnEscape:	true,
		title:			title,
		buttons:		buttonsOpts
	});
}

// Function to simply send an ajaxRequest with option to call functions before and after sending data
function dialog_ajax( title, ajaxUrl, ajaxData, ajaxType, ajaxDataType, beforeSend, afterSend, ajaxjQcontext )
{
	var ajaxUrl			= typeof ajaxUrl == 'undefined' || ajaxUrl === false				? alert( 'You send an invalid url' ) : ajaxUrl,
		ajaxType		= typeof ajaxType == 'undefined' || ajaxType === false				? 'POST' : ajaxType,
		ajaxDataType	= typeof ajaxDataType == 'undefined' || ajaxDataType === false		? 'JSON' : ajaxDataType,
		ajaxjQcontext	= typeof ajaxjQcontext == 'undefined' || ajaxjQcontext === false	? $('document.body') : ajaxjQcontext,
		title			= typeof title == 'undefined' || title === false					? set_popup_title() : title;

    ajaxData['_cat_ajax'] = 1;

	$.ajax({
		type:		ajaxType,
		url:		ajaxUrl,
		dataType:	ajaxDataType,
		context:	ajaxjQcontext,
		data:		ajaxData,
		beforeSend:	function( data )
		{
			// deactive .fc_popup before send data
			$('.fc_popup').remove();
			// Set activity and store in a variable to use it later
			data.process	= set_activity( title );
			// check if a function beforeSend is defined and call it if true
			if ( typeof beforeSend != 'undefined' && beforeSend !== false )
			{
				beforeSend.call(this);
			}
		},
		success:		function( data, textStatus, jqXHR )
		{
			return_success( jqXHR.process , data.message );
			// Check if there is a div.success_box in returned data that implements that the request was completely successful
			if ( data.success === true )
			{
				// check if a function afterSend is defined and call it if true
				if ( typeof afterSend != 'undefined' && afterSend !== false )
				{
					afterSend.call(this, data);
				}
			}
			else {
				// return error
				return_error( jqXHR.process , data.message );
			}
		}
	});
}

// Function to define confirm of forms showing in a dialog and adding a optional beforeSend and afterSend
function dialog_form( currentForm, beforeSend, afterSend, data_type )
{
	if ( typeof data_type == 'undefined' ) {
		var data_type	= 'json';
	}
	// If form is submitted
	currentForm.submit( function(e)
	{
		// Prevent form from being send twice!
		e.preventDefault();
		// Define ajax for form
		currentForm.ajaxSubmit(
		{
			context:		currentForm,
			dataType:		data_type,
			beforeSend:		function( data )
			{
				// Check if the form has a (mostly hidden) input field with a title for the form (if not 'loading' is used
				if ( currentForm.find('input[name=fc_form_title]').size() > 0 )
				{
					var title	= currentForm.find('input[name=fc_form_title]').val();
				}
				else {
					var title = 'Loading';
				}

				// Set activity and store in a variable to use it later
				data.process	= set_activity( title );

				// Destroy dialog to hide the
				if ( currentForm.is(':data(dialog)') ) {
					currentForm.dialog('destroy');
				}

				// check if a function beforeSend is defined and call it if true
				if ( typeof beforeSend != 'undefined' && beforeSend !== false )
				{
					beforeSend.call(this);
				}
			},
			success:		function( data, textStatus, jqXHR )
			{
				// Check if there is a div.success_box in returned data that implements that the request was completely successful
				if ( data.success === true )
				{
					return_success( jqXHR.process , data.message );
					// check if a function afterSend is defined and call it if true
					if ( typeof afterSend != 'undefined' && afterSend !== false )
					{
						afterSend.call(this, data);
					}
				}
				else {
					// else return error
					return_error( jqXHR.process , data.message );
				}
			},
			error:		function( jqXHR, textStatus, errorThrown )
			{
				jqXHR.process.remove();
				console.log(jqXHR);
				console.log(textStatus);
				console.log(errorThrown);
				alert(textStatus + ': ' + errorThrown );
			}
		});
	});
}

// Function to activate buttons (for example if a some new elements where added to the body with ajax)
function set_buttons( element )
{
	// Activate toggle for select
	element.find( '.fc_toggle_element' ).fc_toggle_element();
}

function searchUsers( searchTerm )
{
	$('#fc_list_overview li').removeClass('fc_activeSearch').slideDown(0);
	if ( searchTerm.length > 0 )
	{
		$('#fc_list_overview li:containsi(' + searchTerm + ')').not('.fc_no_search').addClass('fc_activeSearch');
		if ( $('#fc_list_overview').hasClass('fc_settings_list') )
		{
			$('.fc_list_forms:containsi(' + searchTerm + ')').each( function()
			{
				var id	= $(this).prop('id');
				$('input[value*=' + id.substr(8) + ']').closest('li').addClass('fc_activeSearch');
			});
		}
		$('#fc_list_overview li').not('.fc_activeSearch').slideUp(300);
		if ( $('#fc_list_overview li.fc_activeSearch').size() == 1 ){
			$('#fc_list_overview li.fc_activeSearch').click();
		}
	}
	else
	{
		$('#fc_list_overview li').not('fc_no_search').slideDown(300);
	}

}

// Marked as deprecated!

function confirm_link ( message, url )
{
	var afterSend		= function()
	{
		location.reload(true);
	};
    dialog_confirm( message, false, url, false, 'GET', 'HTML', false, afterSend );
}

jQuery(document).ready( function()
{
	// Check if a cookie for sidebar is defined
	if ( typeof $.cookie('sidebar') !== 'undefined' )
	{
		// Get current width of browser and cookie for width of the sidebar
		var window_width = parseInt( $(window).width(), 10 ),
			width	     = $.cookie('sidebar');
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

	// Initial activation of click events
	set_buttons($('body'));

	$('#fc_list_search input').livesearch(
	{
		searchCallback:			searchUsers,
		queryDelay:				250,
		innerText:				CAT_TEXT['SEARCH'],
		minimumSearchLength:	2
	});

	$('.fc_input_fake label').click( function()
	{
		var input	= $(this).prop('for');
		$('#' + input).val('');
		searchUsers('');
	});

	// Activate automatically sending forms with ajax, if they have the class ".ajaxForm"
	$('.ajaxForm').each(function()
	{
		dialog_form( $(this) );
	});

    // Activate Ajax for Links having class 'ajaxLink'
    $('a.ajaxLink').click( function(e) {
        e.preventDefault();
        var ajaxUrl = $(this).prop('href');
        dialog_ajax( 'Saving', ajaxUrl, getDatesFromQuerystring(document.URL.split('?')[1]) );
    });

	// Bind buttons to show popups
	//$('.show_popup').fc_show_popup();


	// Make the sidebar resizeable
	$('#fc_sidebar_footer, #fc_sidebar').resizable({
		handles: 'e',
		minWidth: 100,

		start: function(event, ui)
		{
			// store width of browser
			window_width = parseInt( $(window).width(), 10 );
		},
		resize: function(event, ui)
		{
			// resize also some elements
			$('#fc_content_container, #fc_content_footer').css({width: ( window_width - ui.size.width )+'px'});
			$('#fc_sidebar_footer, #fc_sidebar, #fc_activity, #fc_sidebar_content').css({width: ui.size.width+'px'});
			$('#fc_add_page').css({left: ui.size.width+'px'});
		},
		stop: function(event, ui)
		{
			// Save new width of sidebar in a cookie
			$.cookie('sidebar', ui.size.width, {path: '/'});
		}
	});

	// Bind navigation of AddPage-Form
	$('#fc_add_page_nav').find('a').click( function()
	{
		var current		= $(this);
		if ( !current.hasClass('fc_active') )
		{
			$('#fc_add_page').find('.fc_active').removeClass('fc_active');
			current.addClass('fc_active');
			var target	= current.attr('href');
			$(target).addClass('fc_active');
		}
		return false;
	});

	// Active button in the footer to show System information
	$('#fc_footer_info').on( 'click', '#fc_showFooter_info', function()
	{
		$(this).toggleClass('fc_active').parent('#fc_footer_info').children('ul').slideToggle(300);
	});

});