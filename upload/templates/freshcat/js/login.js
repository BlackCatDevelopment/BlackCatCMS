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

/*
 * jquery.infieldlabel
 * A simple jQuery plugin for adding labels that sit over a form field and fade away when the fields are populated.
 *
 * Copyright (c) 2009 - 2013 Doug Neiner <doug@dougneiner.com> (http://code.dougneiner.com)
 * Source: https://github.com/dcneiner/In-Field-Labels-jQuery-Plugin
 * Dual licensed MIT or GPL
 *   MIT (http://www.opensource.org/licenses/mit-license)
 *   GPL (http://www.opensource.org/licenses/gpl-license)
 *
 * @version 0.1.3
 */
(function(e){e.InFieldLabels=function(n,i,t){var o=this;o.$label=e(n),o.label=n,o.$field=e(i),o.field=i,o.$label.data("InFieldLabels",o),o.showing=!0,o.init=function(){var n;o.options=e.extend({},e.InFieldLabels.defaultOptions,t),setTimeout(function(){""!==o.$field.val()?(o.$label.hide(),o.showing=!1):(o.$label.show(),o.showing=!0)},200),o.$field.focus(function(){o.fadeOnFocus()}).blur(function(){o.checkForEmpty(!0)}).bind("keydown.infieldlabel",function(e){o.hideOnChange(e)}).bind("paste",function(){o.setOpacity(0)}).change(function(){o.checkForEmpty()}).bind("onPropertyChange",function(){o.checkForEmpty()}).bind("keyup.infieldlabel",function(){o.checkForEmpty()}),o.options.pollDuration>0&&(n=setInterval(function(){""!==o.$field.val()&&(o.$label.hide(),o.showing=!1,clearInterval(n))},o.options.pollDuration))},o.fadeOnFocus=function(){o.showing&&o.setOpacity(o.options.fadeOpacity)},o.setOpacity=function(e){o.$label.stop().animate({opacity:e},o.options.fadeDuration),o.showing=e>0},o.checkForEmpty=function(e){""===o.$field.val()?(o.prepForShow(),o.setOpacity(e?1:o.options.fadeOpacity)):o.setOpacity(0)},o.prepForShow=function(){o.showing||(o.$label.css({opacity:0}).show(),o.$field.bind("keydown.infieldlabel",function(e){o.hideOnChange(e)}))},o.hideOnChange=function(e){16!==e.keyCode&&9!==e.keyCode&&(o.showing&&(o.$label.hide(),o.showing=!1),o.$field.unbind("keydown.infieldlabel"))},o.init()},e.InFieldLabels.defaultOptions={fadeOpacity:.5,fadeDuration:300,pollDuration:0,enabledInputTypes:["text","search","tel","url","email","password","number","textarea"]},e.fn.inFieldLabels=function(n){var i=n&&n.enabledInputTypes||e.InFieldLabels.defaultOptions.enabledInputTypes;return this.each(function(){var t,o,a=e(this).attr("for");a&&(t=document.getElementById(a),t&&(o=e.inArray(t.type,i),(-1!==o||"TEXTAREA"===t.nodeName)&&new e.InFieldLabels(this,t,n)))})}})(jQuery);

jQuery(document).ready(function(){

    // check if SSL is available
    if ("https:" != location.protocol) {
        $.ajax(
		{
			type:		'POST',
			url:		CAT_ADMIN_URL + '/login/ajax_check_ssl.php',
            dataType:	'json',
			cache:		false,
            success:    function( data, textStatus, jqXHR  ) {
                if ( data.success === true ) {
                    window.location = location.href.replace('http','https');
                }
            }
        });
    }

	$('label').inFieldLabels();

	$('.fc_loader').addClass('hidden').fadeOut(0);

	setTimeout(function()
	{
		$('#fc_login_username').focus();
	}, 10);

	$('#fc_forms').show();
	$('#fc_login_form').slideUp(0);
	$('#fc_login_form').slideDown(300);

	$('.fc_img_no_save').fadeOut(5000, function()
	{
		$('.fc_img_no_save').removeClass('fc_start_black').fadeIn(0);
	});

	$('#fc_login_forgot_form').slideUp(0);

	$('#fc_login_forgot').click(function()
	{
		$('#fc_login_form').slideUp(300);
		$('#fc_login_forgot_form').slideDown(300);
		$('#fc_forgot').val('').focus();
		return false;
	});
	$('#fc_home_login').click(function()
	{
		$('#fc_login_forgot_form').slideUp(300);
		$('#fc_login_form').slideDown(300);
		$('#fc_login_password').val('');
		if ( $('#fc_login_username').val() !== '' )
		{
			$('#fc_login_password').inFieldLabels().focus();
		}
		else
		{
			$('#fc_login_username').inFieldLabels().focus();
		}
	});


	$('.fc_login_button').click( function(e)
	{
		e.preventDefault();

		var current				= $(this),
			current_form		= current.closest('form'),
			username_fieldname	= current_form.find('input[name=username_fieldname]').val(),
			password_fieldname	= current_form.find('input[name=password_fieldname]').val(),
			dates				= {
				'username_fieldname':	username_fieldname,
				'password_fieldname':	password_fieldname,
                '_cat_ajax':            1
			};
			dates[username_fieldname]	= current_form.find('input[name=' + username_fieldname + ']').val();
			dates[password_fieldname]	= current_form.find('input[name=' + password_fieldname + ']').val();
		if ( dates.password !== '' &&  dates.user !== '' )
		{
			$.ajax(
			{
				type:		'POST',
				context:	current,
				url:		CAT_ADMIN_URL + '/login/ajax_index.php',
				dataType:	'json',
				data:		dates,
				cache:		false,
				beforeSend:	function( data )
				{
					current.fadeOut(0);
					current_form.find('.fc_loader').fadeIn(0).removeClass('hidden');
					$('#fc_message, #fc_message_login').slideUp(0);
				},
				success:	function( data, textStatus, jqXHR  )
				{
					$('.fc_loader').fadeOut(0).addClass('hidden');
					$(this).fadeIn(0);
					if ( data.success === true )
					{
						window.location		= data.url
					}
					else {
						$('#fc_forms').effect( 'shake', { times: 2 }, 300);
						$('#fc_message_login').addClass('icon-warning highlight').removeClass('icon-info').text(' ' + data.message).slideDown(300);
						$('input[name=' + password_fieldname + ']').val('').focus();
					}
				},
				error:		function( jqXHR, textStatus, errorThrown )
				{
					$('.fc_loader').fadeOut(300).addClass('hidden');
					$(this).fadeIn(0);
					alert(textStatus + ': ' + jqXHR.responseText );
				}
			});
		}
	});

	$('.fc_forgot_button').click( function(e)
	{
		e.preventDefault();

		var current	= $(this).closest('form');
		dates			= {
			'email':		$('#fc_forgot').val(),
            '_cat_ajax':    1
		};
		if ( dates.email !== '' )
		{
			$.ajax(
			{
				type:		'POST',
				url:		CAT_ADMIN_URL + '/login/forgot/ajax_forgot.php',
				dataType:	'json',
				data:		dates,
				cache:		false,
				beforeSend:	function( data )
				{
					$('.fc_loader').removeClass('hidden').fadeIn(0);
					$('#fc_message, #fc_message_login').slideUp(0);
				},
				success:	function( data, textStatus, jqXHR  )
				{
					$('.fc_loader').fadeOut(300).addClass('hidden');
			
					if ( data.success === true )
					{
						$('#fc_message').removeClass('icon-warning highlight').addClass('icon-info').text( ' ' + data.message).slideDown(300);
					}
					else {
						$('#fc_forms').effect( 'shake', { times: 2 }, 300);
						$('#fc_message').addClass('icon-warning highlight').removeClass('icon-info').text(' ' + data.message).slideDown(300);
						$('#fc_forgot').focus();
					}
				},
				error:		function(jqXHR, textStatus, errorThrown)
				{
					$('.fc_loader').fadeOut(300).addClass('hidden');
					alert(textStatus + ': ' + errorThrown );
				}
			});
		}
	});
});