/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author			LEPTON Project
 * @copyright		2012, LEPTON Project
 * @link			http://www.LEPTON-cms.org
 * @license			http://www.gnu.org/licenses/gpl.html
 * @license_terms	please see LICENSE and COPYING files in your package
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
					update:			function(event, ui)
					{
						var dates			= {
							'pageid':			$(this).sortable('toArray'),
							'table':			'pages',
                            '_cat_ajax':        1
						};
						$.ajax(
						{
							type:		'POST',
							url:		CAT_ADMIN_URL + '/pages/ajax_reorder.php',
							dataType:	'json',
							data:		dates,
							cache:		false,
							beforeSend:	function( data )
							{
								data.process	= set_activity( 'Reorder pages' );
							},
							success:	function( data, textStatus, jqXHR  )
							{
								var current	= $(this);
								$('.popup').dialog('destroy').remove();
								element.children('span').removeClass('fc_page_loader');
								if ( data.success === true )
								{
									return_success( jqXHR.process , data.message );
									current.slideUp(300, function() { current.remove(); });
								}
								else {
									return_error( jqXHR.process , data.message);
								}
							},
							error:		function(jqXHR, textStatus, errorThrown)
							{
								$('.popup').dialog('destroy').remove();
								alert(textStatus + ': ' + errorThrown );
							}
						});
					}
				});
			}

			element.find('.fc_page_tree_options_open').on( 'click', function(event)
			{
				event.preventDefault();
				var current_button	= $(this),
					page_id			= current_button.closest('li').children('input').val(),
					dates			= {
										'page_id' : page_id,
										'_cat_ajax': 1
									},
					link			= CAT_ADMIN_URL + '/pages/ajax_page_settings.php';
				$('.page_tree_open_options').removeClass('page_tree_open_options');
				current_button.closest('li').addClass('page_tree_open_options');
				$('#fc_addPage_keywords_ul').remove();
				$.ajax(
				{
					type:		'POST',
					url:		link,
					dataType:	'json',
					data:		dates,
					cache:		false,
					beforeSend:	function( data )
					{
						if ( $('#fc_add_page').is(':visible') )
						{
							$('#fc_add_page').stop().animate({width: 'toggle'}, 200);
						}
					},
					success:	function( data, textStatus, jqXHR  )
					{
						var form	= $('#fc_add_page'),
							option	= '<select name="parent" id="fc_addPage_parent">';
						if ( data.visibility == 'deleted' )
						{
							form.find('nav, ul, .fc_changePageOnly, .fc_addPageOnly').hide();
							form.find('.fc_restorePageOnly').show();
						}
						else {
							form.find('.fc_restorePageOnly, .fc_addPageOnly').hide();
							form.find('nav, ul, .fc_changePageOnly').show();
							$.each(data.parent_list, function(index, value)
							{
								option	= option + '<option value="' + value.id + '"';
 								option	= (
                                               value.disabled === true
                                            || value.id == dates.page_id
                                            || value.current_is_parent === true
                                          )
										? option + ' disabled="disabled">'
                                        : option + '>'
                                        ;
								for ( var i = 0; i < value.level; i++ )
								{
									option	= option + '-';
								}
								option	= option + value.menu_title + '</option>';
							});
							option	= option + '</select>';
							$('#fc_addPage_parent').replaceWith(option);

							// Set textfields
							$('#fc_addPage_title').val(data.menu_title);
							$('#fc_addPage_page_title').val(data.page_title);
							$('#fc_addPage_description').val(data.description);
							$('#fc_addPage_keywords').val(data.keywords);
							$('#fc_addPage_page_link').val(data.short_link);
							
							// Set selectfields
							$('#fc_addPage_parent option').removeAttr('selected');
							$('#fc_addPage_parent option[value=' + data.parent + ']').attr('selected', true);
							$('#fc_addPage_menu option').removeAttr('selected');
							$('#fc_addPage_menu option[value=' + data.menu + ']').attr('selected',true);
							$('#fc_addPage_target option').removeAttr('selected');
							$('#fc_addPage_target option[value=' + data.target + ']').attr('selected',true);
							$('#fc_addPage_template option').removeAttr('selected');
							if (data.template === '')
							{
								$('#fc_addPage_template option:first').attr('selected',true);
							}
							else {
								$('#fc_addPage_template option[value=' + data.template + ']').attr('selected',true);
							}
							$('#fc_addPage_language option').removeAttr('selected');
							$('#fc_addPage_language option[value=' + data.language + ']').attr('selected',true);
							$('#fc_addPage_visibility option').removeAttr('selected');
							$('#fc_addPage_visibility option[value=' + data.visibility + ']').attr('selected',true);
							
							// Set checkboxesfields
							$('#fc_addPage_Searching').attr('checked', data.searching);
							$('#fc_addPage_admin_groups input').each( function()
							{
								var current		= $(this),
									currenVal	= current.val(),
									groups		= data.admin_groups;
								if ( $.inArray( currenVal, groups ) )
								{
									current.attr('checked',false);
								}
								else {
									current.attr('checked',true);
								}
							});
							$('#fc_addPage_allowed_viewers input').each( function()
							{
								var current		= $(this),
									currenVal	= current.val(),
									groups		= data.viewing_groups;
								if ( $.inArray( currenVal, groups ) )
								{
									current.attr('checked',false);
								}
								else {
									current.attr('checked',true);
								}
							});
							// Activate tagit for Keywords in the adding
							$('#fc_addPage_keywords_ul').remove();
							$('<ul id="fc_addPage_keywords_ul" />').insertBefore( $('#fc_addPage_keywords') ).tagit(
							{
								allowSpaces:			true,
								singleField:			true,
								singleFieldDelimiter:	',',
								singleFieldNode:		$('#fc_addPage_keywords'),
								beforeTagAdded:			function(event, ui)
								{
									ui.tag.addClass('icon-tag');
								}
							});
						}
						form.animate({width: 'toggle'});
					},
					error:		function(jqXHR, textStatus, errorThrown)
					{
						alert(textStatus + ': ' + errorThrown );
					}
				});
			});

			// bind elements with click event
			element.find('.fc_toggle_tree').on( 'click',  function()
			{
				var clicked_element		= $(this).closest('li'),
					set_cookie			= clicked_element.attr('id');
			
				if ( clicked_element.hasClass('fc_tree_open') )
				{
					clicked_element.removeClass('fc_tree_open').addClass('fc_tree_close');
					$.cookie( set_cookie, null, { path: '/' } );
				}
				else
				{
					clicked_element.addClass('fc_tree_open').removeClass('fc_tree_close');
					$.cookie( set_cookie, 'open', { path: '/' } );
				}
			});

			// Cosmetical class to change white to black arrows ;-)
			element.find('li > a').mouseenter( function()
			{
				$(this).parent('li').addClass('fc_tree_hover');
			}).mouseleave( function ()
			{
				$(this).parent('li').removeClass('fc_tree_hover');
			});
		});
	};
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
				var option			= options.options_ul.find('.fc_activeSearchOption').attr('id'),
					searchTerm		= element.val();

				search_page_tree( searchTerm );

				$('<div id="fc_searchOption" class="fc_br_all fc_border fc_gradient1 fc_gradient_hover"><span class="fc_br_left fc_gradient_blue">' + option + '</span><strong>' + searchTerm + '</strong></div>').prependTo('#fc_search_tree');
				$('#fc_searchOption').click( function()
				{
					search_page_tree( '' );
					element.show().val('').focus();
				});
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
						if ( searchTerm !== '' )
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

					if ( $('#fc_searchOption').size() === 0 && searchTerm === '' )
					{
						options.defaultValue.show();
					}
					else if ( $('#fc_searchOption').size() === 0 )
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
		});
	};
})(jQuery);


jQuery(document).ready(function()
{
	$('#fc_page_tree_top').page_tree();
	$("#fc_search_page_tree").page_treeSearch();
	$('fc_page_tree_not_editable > a').click( function(e)
	{
		e.preventDefault();
		console.log('check');
	});

	$('#fc_add_page').slideUp(0);

	$('#fc_add_page input:reset').click( function()
	{
		var form	= $('#fc_add_page');

		form.find('.fc_addPageOnly').show();
		form.find('.fc_changePageOnly').hide();
		form.animate({ width: 'toggle' },300);

		$('.page_tree_open_options').removeClass('page_tree_open_options');

		// Activate tagit for Keywords in the adding
		$('#fc_addPage_keywords_ul').remove();
		$('#fc_addPage_keywords').val('');
		$('<ul id="fc_addPage_keywords_ul" />').insertBefore( $('#fc_addPage_keywords') ).tagit(
		{
			allowSpaces:			true,
			singleField:			true,
			singleFieldDelimiter:	',',
			singleFieldNode:		$('#fc_addPage_keywords'),
			beforeTagAdded:			function(event, ui)
			{
				ui.tag.addClass('icon-tag');
			}
		});
		var dates	= {
			'_cat_ajax': 1
		};
		$.ajax(
		{
			context:	form,
			type:		'POST',
			url:		CAT_ADMIN_URL + '/pages/ajax_get_dropdown.php',
			dataType:	'json',
			data:		dates,
			cache:		false,
			success:	function( data, textStatus, jqXHR )
			{
				if ( data.success === true )
				{
					var form	= $(this),
						option	= '<select name="parent" id="fc_addPage_parent">',
						page_id	= $('#fc_addPage_parent_page_id').val();
					$.each(data.parent_list, function(index, value)
					{
						option	= option + '<option value="' + value.id + '"';
						option	= value.disabled === true ||
									value.id == dates.page_id ||
									value.current_is_parent === true
									? option + ' disabled="disabled">' : option+ '>';
						for ( var i = 0; i < value.level; i++ )
						{
							option	= option + '-';
						}
						option	= option + value.menu_title + '</option>';
					});
					option	= option + '</select>';
					$('#fc_addPage_parent').replaceWith( option );
					console.log( page_id );
					if ( page_id !== '' )
					{
						$('#fc_addPage_parent').val( page_id );
					}
				}
				else {
					return_error( jqXHR.process , data.message);
				}
			},
			error:		function(jqXHR, textStatus, errorThrown)
			{
				console.log(jqXHR);
				console.log(textStatus);
				console.log(errorThrown);
				alert(textStatus + ': ' + errorThrown );
			}
		});
	});

	$('.fc_side_add').click( function(e)
	{
		e.preventDefault();
		var form	= $('#fc_add_page');
		if ( $('#fc_add_page').is(':visible') )
		{
			$('#fc_add_page').stop().animate({width: 'toggle'}, 100);
		}
		form.find('.fc_restorePageOnly, .fc_changePageOnly').hide();
		form.find('nav, ul, .fc_addPageOnly').show();
		form.find('a:first').click();
		form.find('input:text:first').focus();
		form.find('input:reset').trigger('click');
	});

	$('#fc_addPageSubmit').click( function (e)
	{
		e.preventDefault();
		var current			= $(this),
			current_form	= current.closest('form'),
			current_pT		= $('.page_tree_open_options'),
			admin_groups	= [],
			viewing_groups	= [];

		$('#fc_addPage_admin_groups').children('input:checked').each( function()
		{
			admin_groups.push( $(this).val() );
		});
		$('#fc_addPage_viewers_groups').children('input:checked').each( function()
		{
			viewing_groups.push( $(this).val() );
		});

		var dates	= {
			'page_id':			current_pT.children('input[name=pageid]').val(),
			'page_title':		$('#fc_addPage_page_title').val(),
			'menu_title':		$('#fc_addPage_title').val(),
            'page_link':		$('#fc_addPage_page_link').val(),
			'type':				$('#fc_addPage_type option:selected').val(),
			'parent':			$('#fc_addPage_parent option:selected').val(),
			'menu':				$('#fc_addPage_menu option:selected').val(),
			'target':			$('#fc_addPage_target option:selected').val(),
			'template':			$('#fc_addPage_template option:selected').val(),
			'language':			$('#fc_addPage_language option:selected').val(),
			'description':		$('#fc_addPage_description').val(),
			'keywords':			$('#fc_addPage_keywords').val(),
			'searching':		$('#fc_addPage_Searching').is(':checked') ? 1 : 0,
			'visibility':		$('#fc_addPage_visibility option:selected').val(),
			'page_groups':		$('#fc_addPage_page_groups').val(),
			'visibility':		$('#fc_addPage_visibility option:selected').val(),
			'admin_groups':		admin_groups,
			'viewing_groups':	viewing_groups,
			'_cat_ajax':        1
		};

		$.ajax(
		{
			context:	current_pT,
			type:		'POST',
			url:		CAT_ADMIN_URL + '/pages/ajax_add_page.php',
			dataType:	'json',
			data:		dates,
			cache:		false,
			beforeSend:	function( data )
			{
				data.process	= set_activity( 'Save page' );
			},
			success:	function( data, textStatus, jqXHR  )
			{
				if ( data.success === true )
				{
					return_success( jqXHR.process , data.message );
					var current			= $(this);
					//$('#fc_add_page input[type=reset]').click();
					window.location.replace( data.url );
				}
				else {
					return_error( jqXHR.process , data.message);
				}
			},
			error:		function(jqXHR, textStatus, errorThrown)
			{
				alert(textStatus + ': ' + errorThrown );
			}
		});
	});

	$('#fc_savePageSubmit').click( function (e)
	{
		e.preventDefault();
		var current			= $(this),
			current_form	= current.closest('form'),
			current_pT		= $('.page_tree_open_options'),
			admin_groups	= [],
			viewing_groups	= [];

		$('#fc_addPage_admin_groups').children('input:checked').each( function()
		{
			admin_groups.push( $(this).val() );
		});
		$('#fc_addPage_viewers_groups').children('input:checked').each( function()
		{
			viewing_groups.push( $(this).val() );
		});
		var dates	= {
			'page_id':			current_pT.children('input[name=pageid]').val(),
			'page_title':		$('#fc_addPage_page_title').val(),
			'menu_title':		$('#fc_addPage_title').val(),
			'parent':			$('#fc_addPage_parent option:selected').val(),
			'menu':				$('#fc_addPage_menu option:selected').val(),
			'target':			$('#fc_addPage_target option:selected').val(),
			'template':			$('#fc_addPage_template option:selected').val(),
			'language':			$('#fc_addPage_language option:selected').val(),
			'page_link':		$('#fc_addPage_page_link').val(),
			'description':		$('#fc_addPage_description').val(),
			'keywords':			$('#fc_addPage_keywords').val(),
			'searching':		$('#fc_addPage_Searching').is(':checked') ? 1 : 0,
			'visibility':		$('#fc_addPage_visibility option:selected').val(),
			'page_groups':		$('#fc_addPage_page_groups').val(),
			'visibility':		$('#fc_addPage_visibility option:selected').val(),
			'admin_groups':		admin_groups,
			'viewing_groups':	viewing_groups,
			'_cat_ajax':        1
		};
		$.ajax(
		{
			context:	current_pT,
			type:		'POST',
			url:		CAT_ADMIN_URL + '/pages/ajax_settings_save.php',
			dataType:	'json',
			data:		dates,
			cache:		false,
			beforeSend:	function( data )
			{
				data.process	= set_activity( 'Saving page' );
			},
			success:	function( data, textStatus, jqXHR  )
			{
				if ( data.success === true )
				{
					return_success( jqXHR.process , data.message );
					var current			= $(this),
						new_parent		= $('#pageid_' + data.parent ),
						old_parent		= current.parent().closest('li');

					$('#fc_add_page input:reset').click();

					switch (data.visibility) {
						case 'public':
							var newIcon	= 'icon-screen';
							break;
						case 'private':
							var newIcon	= 'icon-key';
							break;
						case 'registered':
							var newIcon	= 'icon-users';
							break;
						case 'hidden':
							var newIcon	= 'icon-eye-2';
							break;
						case 'deleted':
							var newIcon	= 'icon-remove';
							break;
						default:
							var newIcon	= 'icon-eye-blocked';
							break;
					}
					if ( dates.parent != old_parent.children('input[name=pageid]').val() )
					{
						if ( dates.parent === 0 )
						{
							$('#fc_page_tree_top').children('ul').append( current );
						}
						else if ( new_parent.children('ul').size() > 0 )
						{
							if ( current.siblings('li').size() === 0 )
							{
								old_parent.removeClass('fc_tree_open');
								old_parent.find('ul').remove();
								old_parent.find('.fc_toggle_tree').remove();
							}
							current.appendTo( new_parent.children('ul') );
						}
						else {
							new_parent.children('.fc_page_link').prepend('<span class="fc_toggle_tree" />');
							$('<ul class="ui-sortable" />').appendTo( new_parent ).append( current );
						}
						new_parent.parentsUntil('#fc_page_tree_top', 'li').andSelf().addClass('fc_expandable fc_tree_open').removeClass('fc_tree_close');
					}
					current.children('dl').children('.fc_search_MenuTitle').text( data.menu_title );
					current.children('dl').children('.fc_search_PageTitle').text( data.page_title );
					current.children('.fc_page_link').children('a').children('.fc_page_tree_menu_title').removeClass().addClass('fc_page_tree_menu_title ' + newIcon).text( ' ' + data.menu_title );
					current.children('.fc_page_link > a:first').attr( 'title', 'Page title: ' + data.page_title );
				}
				else {
					return_error( jqXHR.process , data.message);
				}
			},
			error:		function(jqXHR, textStatus, errorThrown)
			{
				alert(textStatus + ': ' + errorThrown );
			}
		});
	});

	$('#fc_removePageSubmit').click( function (e)
	{
		e.preventDefault();
		var current			= $(this),
			current_form	= current.closest('form'),
			current_pT		= $('.page_tree_open_options'),
			dates	= {
				'page_id':			current_pT.children('input[name=pageid]').val(),
				'_cat_ajax':        1
			},
			afterSend		= function( data, textStatus, jqXHR )
			{
				$('#fc_add_page input[type=reset]').click();
				var current		= $(this);
				if ( data.success === true && data.status === 0 )
				{
					current.remove();
				}
				else {
					current.find('.fc_page_link').find('.fc_page_tree_menu_title').removeClass().addClass('fc_page_tree_menu_title icon-remove');
				}
			};

		dialog_confirm( 'Do you really want to delete this page?', 'Removing page', CAT_ADMIN_URL + '/pages/ajax_delete_page.php', dates, 'POST', 'JSON', false, afterSend, current_pT );
	});

	$('#fc_restorePageSubmit').click( function (e)
	{
		e.preventDefault();
		var current			= $(this),
			current_form	= current.closest('form'),
			current_pT		= $('.page_tree_open_options'),
			dates	= {
				'page_id':			current_pT.children('input[name=pageid]').val(),
				'_cat_ajax':        1
			},
			afterSend		= function( data, textStatus, jqXHR )
			{
				$('#fc_add_page input[type=reset]').click();
				var current		= $(this);
				if ( data.success === true )
				{
					current.find('.fc_page_link').find('.fc_page_tree_menu_title').removeClass().addClass('fc_page_tree_menu_title icon-screen');
				}
				else {
					return_error( jqXHR.process , data.message);
				}
			};
		dialog_ajax( 'Restoring page', CAT_ADMIN_URL + '/pages/ajax_restore_page.php', dates, 'POST', 'JSON', false, afterSend, current_pT );
	});
	
	$('#fc_addPageChildSubmit').click( function(e)
	{
		e.preventDefault();
		$('#fc_addPage_parent_page_id').val( $('.page_tree_open_options').children('input[name=pageid]').val() );
		$('.fc_side_add').click();
	});
});