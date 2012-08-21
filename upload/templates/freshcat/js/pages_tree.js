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

(function ($) {
	$.fn.page_tree = function (options)
	{
		var defaults =
		{
			beforeSend:		function(){},
			afterSend:		function(){}
		};
		var options = $.extend(defaults, options);
		return this.each(function ()
		{
			// Storing $(this) in a variable
			var element		= $(this);

			if ( element.find('li').size() > 1 )
			{
				element.find('ul').sortable({
					cancel:			'.ui-state-disabled',
					helper:			'clone',
					handle:			'div.fc_page_link',
					axis:			'y',
					update:			function(event, ui) {
						var dates		= $(this).sortable("serialize")+'&table=pages';
						var link		= ADMIN_URL+'/pages/reorder.php';

						var beforeSend	= function ()
						{
							process_div = set_activity();
						}

						var afterSend	= function ()
						{
							element.children('span').removeClass('fc_page_loader');
						}

						dialog_ajax(link,dates,beforeSend,afterSend,element);

					}
				});
			}

			element.find('.fc_page_tree_options .fc_close').click( function()
			{
				$('.page_tree_open_options').removeClass('page_tree_open_options');
				$(this).closest('li').children('.fc_page_tree_options_parent').fadeOut(300);
			})

			element.find('.fc_page_tree_options_open').click(function()
			{
				$('.page_tree_open_options').removeClass('page_tree_open_options');
				$('.fc_page_tree_options_parent').fadeOut(400);
				var current_button			= $(this);
				var options_container		= current_button.closest('li').children('.fc_page_tree_options_parent');

				current_button.closest('li').addClass('page_tree_open_options');

				options_container.fadeIn(50).position(
				{
					of:		options_container.closest( 'li' ),
					my:		'left top',
					at:		'right top',
					offset:	'5 -5'
				});

				return false;
			});

			// bind elements with click event
			element.find('li').click( function()
			{
				// Storing $(this) in a variable
				var clicked_element			= $(this);

				clicked_element.children('.fc_page_link').find('.fc_toggle_tree').unbind().click( function()
				{
					var page_id		= clicked_element.attr('rel');

					if ( clicked_element.hasClass('fc_tree_open') )
					{
						clicked_element.removeClass('fc_tree_open').addClass('fc_tree_close');
						$.cookie( 'p' + page_id, null, { path: '/' } );
						//clicked_element.children('ul').addClass('fc_page_inactive');
					}
					else
					{
						clicked_element.addClass('fc_tree_open').removeClass('fc_tree_close');
						$.cookie( 'p' + page_id, 'open', { path: '/' } );
					}
				});
				/*clicked_element.children('span > a').click( function(){
					
					// return false;
				});*/
			}).click();

			// Cosmetical class to change white to black arrows ;-)
			element.find('li > a').mouseenter( function()
			{
				$(this).parent('li').addClass('fc_tree_hover');
			}).mouseleave( function ()
			{
				$(this).parent('li').removeClass('fc_tree_hover');
			});
		})
	}
})(jQuery);

(function ($) {
	$.fn.page_treeSearch = function (options)
	{
		var defaults =
		{
			options_ul:		$('#fc_search_page_options'),
			page_tree:		$('#fc_page_tree_top'),
			defaultValue:	$('#fc_search_page_tree_default')
		};
		var options = $.extend(defaults, options);
		return this.each(function ()
		{
			// Storing $(this) in a variable
			var element		= $(this);
			element.val('').hide();

			function search_page_tree( searchTerm )
			{
				var page_tree_li		= options.page_tree.find('li');
				page_tree_li.removeClass('fc_activeSearch fc_inactiveSearch fc_matchedSearch');
				options.page_tree.removeHighlight();

				if ( searchTerm.length > 0 )
				{
					page_tree_li.addClass('fc_inactiveSearch');

					var searchOption	= $('.fc_activeSearchOption').index();

					options.options_ul.slideDown(300);
					options.options_ul.find('li').each( function()
					{
						var current		= $(this);
						current.text( current.attr('title') + ' ' + searchTerm );
					});
					options.options_ul.find('li').smartTruncation();

					switch( parseInt(searchOption) )
					{
						case 0:
							$('dd:containsi(' + searchTerm + ')')
								.parents('li').addClass('fc_activeSearch').removeClass('fc_inactiveSearch');
							options.page_tree.highlight(searchTerm);
							options.page_tree.find('.highlight').closest('li').addClass('fc_matchedSearch');
							break;
						case 1:
							$('dd.fc_search_MenuTitle:containsi(' + searchTerm + ')')
								.parents('li').addClass('fc_activeSearch').removeClass('fc_inactiveSearch');
							options.page_tree.highlight(searchTerm);
							options.page_tree.find('.highlight').closest('li').addClass('fc_matchedSearch');
							break;
						case 2:
							$('dd.fc_search_PageTitle:containsi(' + searchTerm + ')')
								.closest('li').addClass('fc_activeSearch fc_matchedSearch').removeClass('fc_inactiveSearch')
								.parents('li').addClass('fc_activeSearch').removeClass('fc_inactiveSearch');
							break;
						case 3:
							$('dd.fc_search_SectionName:containsi(' + searchTerm + ')')
								.closest('li').addClass('fc_activeSearch fc_matchedSearch').removeClass('fc_inactiveSearch')
								.parents('li').addClass('fc_activeSearch').removeClass('fc_inactiveSearch');
							break;
						case 4:
							$('dd.fc_search_PageID').each( function()
							{
								current_search_item		= $(this);
								if ( current_search_item.text() == searchTerm)
								{
									current_search_item.closest('li').addClass('fc_activeSearch fc_matchedSearch').removeClass('fc_inactiveSearch')
									.parents('li').addClass('fc_activeSearch').removeClass('fc_inactiveSearch');
								}
							});
							break;
						case 5:
							$('dd.fc_search_SectionID').each( function()
							{
								current_search_item		= $(this);
								if ( current_search_item.text() == searchTerm)
								{
									current_search_item.closest('li').addClass('fc_activeSearch fc_matchedSearch').removeClass('fc_inactiveSearch')
									.parents('li').addClass('fc_activeSearch').removeClass('fc_inactiveSearch');
								}
							});
							break;
						default:
							break;
					}
				}
				else 
				{
					$('.fc_activeSearchOption').removeClass('fc_activeSearchOption');
					options.options_ul.find('li:first').addClass('fc_activeSearchOption');
					options.options_ul.slideUp(300);
					$('#fc_searchOption').remove();
				}
			}

			function setSearchTreeOption()
			{
				var option					= options.options_ul.find('.fc_activeSearchOption').attr('rel'),
					searchTerm				= element.val();

				search_page_tree( searchTerm );

				$('<div id="fc_searchOption" class="ui-corner-all"><span class="ui-corner-left">' + option + '</span><strong>' + searchTerm + '</strong></div>').prependTo('#fc_search_tree');
				$('#fc_searchOption').click( function()
				{
					search_page_tree( '' );
					element.show().val('').focus();
				})
			}

			element.keyup(function(e) {
				switch (e.keyCode)
				{
					case 40:	// Key down
						if ( !options.options_ul.find('li:last').hasClass('fc_activeSearchOption') )
						{
							$('.fc_activeSearchOption').removeClass('fc_activeSearchOption').next('li').addClass('fc_activeSearchOption');
						}
						search_page_tree( element.val() );
						break;
					case 38:	// Key up
						if ( !options.options_ul.find('li:first').hasClass('fc_activeSearchOption') )
						{
							$('.fc_activeSearchOption').removeClass('fc_activeSearchOption').prev('li').addClass('fc_activeSearchOption');
						}
						search_page_tree( element.val() );
						break;
					case 13:	// Key up
						setSearchTreeOption();
						element.val('').hide().blur();
						//$('#fc_search_tree').addClass('fc_page_tree_searchActive');
						break;
					default:
						var searchTerm	= element.val();
						if ( searchTerm != '' )
						{
							search_page_tree( searchTerm );
						}
						break;
				}
			});

			options.defaultValue.click( function()
			{
				options.defaultValue.hide();
				search_page_tree( '' );
				element.show().val('').focus();
			});

			element.blur( function()
			{
				setTimeout( function() {
					var searchTerm	= element.val();

					if ( $('#fc_searchOption').size() == 0 && searchTerm == '' )
					{
						options.defaultValue.show();
					}
					else if ( $('#fc_searchOption').size() == 0 )
					{
						setSearchTreeOption();
						element.val('').hide();
					}
					options.options_ul.slideUp(300);
				}, 300);
			});

			$('#fc_search_tree .fc_close').click( function()
			{
				search_page_tree( '' );
				element.show().val('').focus();
			});

			options.options_ul.find('li').click( function()
			{
				$('.fc_activeSearchOption').removeClass('fc_activeSearchOption');
				$(this).addClass('fc_activeSearchOption');

				setSearchTreeOption();
				options.options_ul.slideUp( 300 );
				element.hide().val('');
			});
		})
	}
})(jQuery);


jQuery(document).ready(function()
{
	$('#fc_page_tree_top').page_tree();
	$("#fc_search_page_tree").page_treeSearch();
	$('fc_page_tree_not_editable > a').click( function()
	{
		return false;
	});

	$('.fc_page_tree_save_and_close').click( function ()
	{
		var current			= $(this),
			page_id			= current.closest('li').attr('rel'),
			menu_title		= current.closest('.fc_page_tree_options').find('input[name=MenuTitle]').val(),
			page_title		= current.closest('.fc_page_tree_options').find('input[name=PageTitle]').val(),
			parent			= current.closest('.fc_page_tree_options').find('input[name=parent]').val(),
			page_link		= current.closest('.fc_page_tree_options').find('input[name=PageLink]').val(),
			dates			= 'page_id=' + page_id + '&menu_title=' + menu_title + '&page_title=' + page_title + '&parent=' + parent + '&page_link=' + page_link + '&request_from=ajax',
			link			= ADMIN_URL + '/pages/settings_save.php',
			beforeSend		= function ()
								{
									process_div		= set_activity('Save settings');
								},
			afterSend		= function ()
								{
									var currentStyle	= $(this),
										menu_title		= current.closest('.fc_page_tree_options').find('input[name=MenuTitle]').val(),
										page_title		= current.closest('.fc_page_tree_options').find('input[name=PageTitle]').val(),
										parent			= current.closest('li');

									$('.page_tree_open_options').removeClass('page_tree_open_options');

									parent.children('.fc_page_tree_options_parent').fadeOut(300);

									parent.find('.fc_page_tree_search_dl > .fc_search_MenuTitle:first').html( menu_title );
									parent.find('.fc_page_tree_search_dl > .fc_search_PageTitle:first').html( page_title );
									parent.find('.fc_page_link > a > .fc_page_tree_menu_title:first').html( menu_title );
									parent.find('.fc_page_link > a:first').attr( 'title', leptranslate('Page title') + ': ' + page_title );
									//alert(parent.find('.fc_page_tree_search_dl > .fc_search_MenuTitle').html());
								};
		dialog_ajax( link, dates, beforeSend, afterSend, current, 'POST' );
	});

	$('.page_tree_delete_page').click( function ()
	{
		var current			= $(this),
			page_id			= current.closest('li').attr('rel'),
			link			= ADMIN_URL + '/pages/delete.php?page_id=' + page_id + '&request_from=ajax',
			message			= 'Are you sure you want to delete this page and all child pages?',
			afterSend		= function ()
								{
									var current		= $(this).closest('li');
									$('.page_tree_open_options').removeClass('page_tree_open_options');
									if ( current.children('.fc_page_link').hasClass('fc_page_type_deleted') )
									{
										current.remove();
									}
									else
									{
										current.find('.fc_page_tree_quick_changes').addClass('hidden');
										current.find('.fc_page_tree_restore').removeClass('hidden');
										current.children('.fc_page_tree_options_parent').fadeOut(300);
										current.find('.fc_page_link').addClass('fc_page_type_deleted');
									}
								};
		dialog_confirm(message,link,false,afterSend,current);
	});

	$('.fc_page_tree_restore_page').click( function ()
	{
		var current			= $(this),
			page_id			= current.closest('li').attr('rel'),
			link			= ADMIN_URL + '/pages/restore.php',
			dates			= 'page_id=' + page_id,
			afterSend		= function ()
								{
									var current		= $(this).closest('li');
									$('.page_tree_open_options').removeClass('page_tree_open_options');
									current.find('.fc_page_tree_quick_changes').removeClass('hidden');
									current.find('.fc_page_tree_restore').addClass('hidden');
									current.children('.fc_page_tree_options_parent').fadeOut(300);
									current.find('.fc_page_link').removeClass('fc_page_type_deleted');
								};
		dialog_ajax( link, dates, false, afterSend, current, 'POST' );
	});
});