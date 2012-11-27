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
 * @version			$Id$
 *
 */


/*
 * In-Field Label jQuery Plugin
 * http://fuelyourcoding.com/scripts/infield.html
 *
 * Copyright (c) 2009 Doug Neiner
 * Dual licensed under the MIT and GPL licenses.
 * Uses the same license as jQuery, see:
 * http://docs.jquery.com/License
 *
 * @version 0.1
 */
(function($){
	$.InFieldLabels = function(label,field, options){
		// To avoid scope issues, use 'base' instead of 'this'
		// to reference this class from internal events and functions.
		var base = this;
		
		// Access to jQuery and DOM versions of each element
		base.$label = $(label);
		base.label = label;

 		base.$field = $(field);
		base.field = field;
		
		base.$label.data("InFieldLabels", base);
		base.showing = true;
		
		base.init = function(){
			// Merge supplied options with default options
			base.options = $.extend({},$.InFieldLabels.defaultOptions, options);

			// Check if the field is already filled in
			if(base.$field.val() != ""){
				base.$label.hide();
				base.showing = false;
			};
			
			base.$field.focus(function(){
				base.fadeOnFocus();
			}).blur(function(){
				base.checkForEmpty(true);
			}).bind('keydown.infieldlabel',function(e){
				// Use of a namespace (.infieldlabel) allows us to
				// unbind just this method later
				base.hideOnChange(e);
			}).change(function(e){
				base.checkForEmpty();
			}).bind('onPropertyChange', function(){
				base.checkForEmpty();
			});
		};

		// If the label is currently showing
		// then fade it down to the amount
		// specified in the settings
		base.fadeOnFocus = function(){
			if(base.showing){
				base.setOpacity(base.options.fadeOpacity);
			};
		};
		
		base.setOpacity = function(opacity){
			base.$label.stop().animate({ opacity: opacity }, base.options.fadeDuration);
			base.showing = (opacity > 0.0);
		};
		
		// Checks for empty as a fail safe
		// set blur to true when passing from
		// the blur event
		base.checkForEmpty = function(blur){
			if(base.$field.val() == ""){
				base.prepForShow();
				base.setOpacity( blur ? 1.0 : base.options.fadeOpacity );
			} else {
				base.setOpacity(0.0);
			};
		};
		
		base.prepForShow = function(e){
			if(!base.showing) {
				// Prepare for a animate in...
				base.$label.css({opacity: 0.0}).show();
				
				// Reattach the keydown event
				base.$field.bind('keydown.infieldlabel',function(e){
					base.hideOnChange(e);
				});
			};
		};

		base.hideOnChange = function(e){
			if(
				(e.keyCode == 16) || // Skip Shift
				(e.keyCode == 9) // Skip Tab
			  ) return; 
			
			if(base.showing){
				base.$label.hide();
				base.showing = false;
			};
			
			// Remove keydown event to save on CPU processing
			base.$field.unbind('keydown.infieldlabel');
		};
  	
		// Run the initialization method
		base.init();
	};
	
	$.InFieldLabels.defaultOptions = {
		fadeOpacity: 0.5, // Once a field has focus, how transparent should the label be
		fadeDuration: 300 // How long should it take to animate from 1.0 opacity to the fadeOpacity
	};
	

	$.fn.inFieldLabels = function(options){
		return this.each(function(){
			// Find input or textarea based on for= attribute
			// The for attribute on the label must contain the ID
			// of the input or textarea element
			var for_attr = $(this).attr('for');
			if( !for_attr ) return; // Nothing to attach, since the for field wasn't used
			
			
			// Find the referenced input or textarea element
			var $field = $(
				"input#" + for_attr + "[type='text']," + 
				"input#" + for_attr + "[type='password']," + 
				"textarea#" + for_attr
				);
				
			if( $field.length == 0) return; // Again, nothing to attach
			
			// Only create object for input[text], input[password], or textarea
			(new $.InFieldLabels(this, $field[0], options));
		});
	};
	
})(jQuery);

function dialog_ajax(link,dates,beforeSend,afterSend){
	$.ajax({
		type: 'GET',
		url: link,
		dataType: 'html',
		data: dates,
		beforeSend: function(data)
		{
			if ( typeof(beforeSend) != 'undefined' && beforeSend != false )
			{
				beforeSend.call(this);
			}
		},
		success: function(data)
		{
			//$('.popup').html($(data).find('#main_content').parent().html());
		},
		error: function(data)
		{
			return_error(data);
		}
	});
}

jQuery(document).ready(function(){

	$('label').inFieldLabels();

	$('.loader').addClass('hidden').fadeOut(0);

	setTimeout(function()
	{
		$('#fc_login_username').focus();
	}, 10);

	$('#fc_forms').slideUp(0);
	$('#fc_forms').slideDown(300);

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

	$('.ajaxLogin').submit( function()
	{
		var current					= $(this),
			username_fieldname		= current.find('input[name=username_fieldname]').val(),
			password_fieldname		= current.find('input[name=password_fieldname]').val(),
			user					= current.find('input[name=' + username_fieldname + ']').val(),
			password				= current.find('input[name=' + password_fieldname + ']').val(),
			dates					= 'username_fieldname=' + username_fieldname + '&password_fieldname=' + password_fieldname + '&' + username_fieldname + '=' + user + '&' + password_fieldname + '=' + password,
			link					= ADMIN_URL + '/login/index_ajax.php';
			current.find('button').fadeOut(0);
			current.find('.loader').fadeIn(0).removeClass('hidden');

		if ( password != '' )
		{
			// Formular abschicken
			$.ajax(
			{
				type:		'POST',
				url:		link,
				data:		dates,
				dataType:	'json',
				async:		false,
				beforeSend:	function(data)
				{
					$('.loader').removeClass('hidden').fadeIn(0);
					$('#fc_message').slideUp(0);
				},
				success:	function(data)
				{
					alert(data.token);
					$('.loader').fadeOut(300).addClass('hidden');
					if ( data.token == false )
					{
						$('#fc_forms').effect( 'shake', { times: 3 }, 80);
						data = 'Something went wrong!';
						$('#fc_message').html(data).slideDown(300);
						$('input[name=' + password_fieldname + ']').val('').focus();
						current.find('.loader').fadeOut(0).addClass('hidden');
						current.find('button').fadeIn(0);
					}
					else
					{
						window.location		= ADMIN_URL + '/start/index.php?leptoken=' + data.token;
					}
				},
				error:		function(data)
				{
					alert(data);
					$('.loader').fadeOut(300).addClass('hidden');
					$('#fc_message').html('Something went wrong!').slideDown(300);
				}
			});
		}
		return false;
	});

	$('.ajaxForm').submit( function()
	{
		var current = $(this);

		dates		= 'email='+$('#fc_forgot').val();
		link		= ADMIN_URL+'/login/forgot/index.php';
		if ( dates != 'email=' )
		{
			// Formular abschicken
			$.ajax({
				type:		'POST',
				url:		link,
				data:		dates,
				dataType:	'html',
			
				beforeSend:	function(data)
				{
					$('.loader').removeClass('hidden').fadeIn(0);
					$('#fc_message').slideUp(0);
				},
			
				success:	function(data)
				{
					$('.loader').fadeOut(300).addClass('hidden');

					if ( data == '' )
					{
						data = 'Something went wrong!';
					}
					$('#fc_message').html(data).slideDown(300);
				},

				error:		function(data)
				{
					$('.loader').fadeOut(300).addClass('hidden');
					$('#fc_message').html('Something went wrong!').slideDown(300);
				}
			});
		}
		return false;
	});
});