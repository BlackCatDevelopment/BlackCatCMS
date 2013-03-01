/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author		  LEPTON Project
 * @copyright	   2012, LEPTON Project
 * @link			http://www.LEPTON-cms.org
 * @license		 http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version			$Id$
 *
 */


/*
 In-Field Label jQuery Plugin
 http://fuelyourcoding.com/scripts/infield.html

 Copyright (c) 2009 Doug Neiner
 Dual licensed under the MIT and GPL licenses.
 Uses the same license as jQuery, see:
 http://docs.jquery.com/License

*/
(function(d){d.InFieldLabels=function(e,b,f){var a=this;a.$label=d(e);a.label=e;a.$field=d(b);a.field=b;a.$label.data("InFieldLabels",a);a.showing=true;a.init=function(){a.options=d.extend({},d.InFieldLabels.defaultOptions,f);if(a.$field.val()!==""){a.$label.hide();a.showing=false}a.$field.focus(function(){a.fadeOnFocus()}).blur(function(){a.checkForEmpty(true)}).bind("keydown.infieldlabel",function(c){a.hideOnChange(c)}).bind("paste",function(){a.setOpacity(0)}).change(function(){a.checkForEmpty()}).bind("onPropertyChange",
function(){a.checkForEmpty()})};a.fadeOnFocus=function(){a.showing&&a.setOpacity(a.options.fadeOpacity)};a.setOpacity=function(c){a.$label.stop().animate({opacity:c},a.options.fadeDuration);a.showing=c>0};a.checkForEmpty=function(c){if(a.$field.val()===""){a.prepForShow();a.setOpacity(c?1:a.options.fadeOpacity)}else a.setOpacity(0)};a.prepForShow=function(){if(!a.showing){a.$label.css({opacity:0}).show();a.$field.bind("keydown.infieldlabel",function(c){a.hideOnChange(c)})}};a.hideOnChange=function(c){if(!(c.keyCode===
16||c.keyCode===9)){if(a.showing){a.$label.hide();a.showing=false}a.$field.unbind("keydown.infieldlabel")}};a.init()};d.InFieldLabels.defaultOptions={fadeOpacity:0.5,fadeDuration:300};d.fn.inFieldLabels=function(e){return this.each(function(){var b=d(this).attr("for");if(b){b=d("input#"+b+"[type='text'],input#"+b+"[type='search'],input#"+b+"[type='tel'],input#"+b+"[type='url'],input#"+b+"[type='email'],input#"+b+"[type='password'],textarea#"+b);b.length!==0&&new d.InFieldLabels(this,b[0],e)}})}})(jQuery);

jQuery(document).ready(function(){

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
		if ( $('#fc_login_username').val() != '' )
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
		if ( dates.password != '' &&  dates.user != '' )
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
						$('#fc_forms').effect( 'shake', { times: 2 }, 400);
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
		if ( dates.email != '' )
		{
			$.ajax(
			{
				type:		'POST',
				url:		CAT_ADMIN_URL + '/login/forgot/index.php',
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