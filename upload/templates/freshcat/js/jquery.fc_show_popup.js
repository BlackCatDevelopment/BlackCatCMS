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

(function ($) {
	$.fn.fc_show_popup = function (options)
	{
		var defaults =
		{
			functionOpen:	false
		};
		var options = $.extend(defaults, options);
		return this.each(function ()
		{

			var current_button		= $(this);
            var form_id             = match_class_prefix('form_',current_button);
            var current_item        = $('#'+form_id);

			current_button.unbind().click(function()
			{
				// remove validation classes -> this is for optical reset of forms
				current_item.find('.fc_valid, .fc_invalid').removeClass('fc_invalid fc_valid');
				
				// Find the title of the element, get it to add it to the ui-dialog-titlebar
				var title	= current_item.find('input[name="form_title"]').val();

                buttonsOpts			= new Array();
				// Find confirm-buttons of the element, get them for adding them to the ui-dialog-footer and hide it
				if ( current_item.find('.fc_confirm_bar').size() > 0 )
				{
					current_item.find('.fc_confirm_bar input').each(function()
					{
						var input		= $(this);
						// bind hidden inputs with empty function and add class hidden to hide them in the dialog form
						if ( input.hasClass( 'hidden' ) )
						{
							var action			= function(){ },
								buttonClass		= 'hidden';
						}
						// bind reset buttons
						else if ( input.prop('type') == 'reset' )
						{
							var action			= function()
							{
								current_item.find('input[type="reset"]').click(); 
								// current_item.find('.fc_individual').removeClass('fc_individual');
								current_item.dialog('close'); 
							},
								buttonClass		= 'reset';
						}
						// else bind submit buttons
						else //if ( $(this).prop('type')=='submit' )
						{
							var action			= function(){
								current_item.submit();
							},
								buttonClass		= 'submit';
						}
						buttonsOpts.push(
						{
							'text':		input.val(),
							'click':	action,
							'class':	buttonClass
						});
					});
					current_item.find('.fc_confirm_bar').hide();
				}

				// activate dialog for the form
				current_item.dialog(
				{
					create:			function(event, ui)
					{
						// again change of classes for optical reason only
						$('.ui-widget-header').removeClass('ui-corner-all').addClass('ui-corner-top');
					},
					open:			function(event, ui)
					{
						if ( typeof(functionOpen) != 'undefined' && functionOpen !== false )
						{
							functionOpen.call(this);
						}
					},
					modal:			true,
					closeOnEscape:	true,
					title:			title,
					minWidth:		600,
					minHeight:		400,
					buttons:		buttonsOpts
				});
			});
//			current_item.hide();
		});
	};
})(jQuery);