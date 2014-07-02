    // --------------------------------
    // v1.1: manage page header files
    // --------------------------------

jQuery(document).ready(function($)
{

    // add jQuery plugin
    if($('button.fcAddPlugin').length) {
        $('button.fcAddPlugin').unbind('click').click(function(e) {
            e.preventDefault();
            var page_id = $('#fc_headers_pageid').text();
            if(page_id === '') {
                page_id = 0;
            }
            $('div#dlgAddPlugin').dialog({
                modal: true
                ,title: cattranslate('Add jQuery Plugin')
                ,width: 600
                ,height: 250
                ,buttons: [
                    {
                        text: cattranslate("Save"),
                        click: function() {
                            $(this).dialog("close");
                            $.ajax(
            				{
            					type:		'POST',
            					url:		CAT_ADMIN_URL + '/pages/ajax_headers.php',
            					dataType:	'json',
            					data:		{
                                    page_id    : page_id,
                                    add_plugin : $('div#dlgAddPlugin').find('select:first').val(),
                                    '_cat_ajax': 1
                                },
            					cache:		false,
            					beforeSend:	function( data )
            					{
            						data.process	= set_activity( 'Adding...' );
            					},
            					success:	function( data, textStatus, jqXHR  )
            					{
                                    $('.popup').dialog('destroy').remove();
                                    if ( data.success === true )
            						{
            							location.reload(true);
            						}
            						else {
            							return_error( jqXHR.process , data.message );
            						}
                                }
                            });
                        }
                    }, {
                        text: cattranslate("Close"),
                        click: function() {
                            $(this).dialog("close");
                        }
                    }
                ]
            });
        });
    }

    // remove jQuery Plugin
    if($('button.fcDelPlugin').length) {
        $('button.fcDelPlugin').unbind('click').click(function(e) {
            e.preventDefault();
            var page_id = $('#fc_headers_pageid').text();
            if(page_id === '') {
                page_id = 0;
            }
            $('div#dlgAddPlugin').find('span.warning').hide();
            $('div#dlgAddPlugin').dialog({
                modal: true
                ,width: 600
                ,height: 250
                ,title: cattranslate('Remove plugin')
                ,buttons: [
                    {
                        text: cattranslate("Save"),
                        click: function() {
                            $(this).dialog("close");
                            $.ajax(
            				{
            					type:		'POST',
            					url:		CAT_ADMIN_URL + '/pages/ajax_headers.php',
            					dataType:	'json',
            					data:		{
                                    page_id    : page_id,
                                    del_plugin : $('div#dlgAddPlugin').find('select:first').val(),
                                    '_cat_ajax': 1
                                },
            					cache:		false,
            					beforeSend:	function( data )
            					{
            						data.process	= set_activity( 'Removing...' );
            					},
            					success:	function( data, textStatus, jqXHR  )
            					{
                                    $('.popup').dialog('destroy').remove();
                                    if ( data.success === true )
            						{
            							location.reload(true);
            						}
            						else {
            							return_error( jqXHR.process , data.message );
            						}
                                }
                            });
                        }
                    }, {
                        text: cattranslate("Close"),
                        click: function() {
                            $(this).dialog("close");
                        }
                    }
                ]
            });
        });
    };

    // make file lists sortable
    if($('#fc_pages_headerfiles_js').length) {
        $('ul.ui-sortable').sortable({
            items:              "> li",
       		axis:				'y',
    		cursor:				'move',
    		helper:				'original',
    		placeholder:		'fc_sortable_placeholder fc_gradient1 fc_br_all fc_shadow_small',
    		forceHelperSize:	true,
            update: function( event, ui ) {
                var dates = {
					'order':			$(this).sortable('toArray'),
                    'page_id':          $('#fc_headers_pageid').text(),
                    'type':             ui.item.prop('class').replace('page_',''),
                    '_cat_ajax':        1
				};
                $.ajax(
				{
					type:		'POST',
					url:		CAT_ADMIN_URL + '/pages/ajax_headers.php',
					dataType:	'json',
					data:		dates,
					cache:		false,
					beforeSend:	function( data )
					{
						data.process	= set_activity( 'Reorder...' );
					},
					success:	function( data, textStatus, jqXHR  )
					{
                        $('.popup').dialog('destroy').remove();
                        if ( data.success === true )
						{
							return_success( jqXHR.process, data.message );
						}
						else {
							return_error( jqXHR.process , data.message );
						}
                    }
                });
            }
        }).disableSelection();

        // add JS
        $('button#fcAddJS').unbind('click').click(function(e) {
            e.preventDefault();
            var page_id = $('#fc_headers_pageid').text();
            if(page_id === '') {
                page_id = 0;
            }
            $('div#dlgAddJS').dialog({
                modal: true
                ,width: 600
                ,height: 150
                ,buttons: [
                    {
                        text: cattranslate("Save"),
                        click: function() {
                            $(this).dialog("close");
                            $.ajax(
            				{
            					type:		'POST',
            					url:		CAT_ADMIN_URL + '/pages/ajax_headers.php',
            					dataType:	'json',
            					data:		{
                                    page_id    : page_id,
                                    add_js_file: $('div#dlgAddJS').find('select:first').val(),
                                    '_cat_ajax': 1
                                },
            					cache:		false,
            					beforeSend:	function( data )
            					{
            						data.process	= set_activity( 'Adding...' );
            					},
            					success:	function( data, textStatus, jqXHR  )
            					{
                                    $('.popup').dialog('destroy').remove();
                                    if ( data.success === true )
            						{
            							location.reload(true);
            						}
            						else {
            							return_error( jqXHR.process , data.message );
            						}
                                }
                            });
                        }
                    }, {
                        text: cattranslate("Close"),
                        click: function() {
                            $(this).dialog("close");
                        }
                    }
                ]
            });
        });

        // remove JS
        $('.fcDelJS').unbind('click').click(function(e) {
            e.preventDefault();
            var page_id = $('#fc_headers_pageid').text();
            if(page_id === '') {
                page_id = 0;
            }
            $.ajax(
				{
					type:		'POST',
					url:		CAT_ADMIN_URL + '/pages/ajax_headers.php',
					dataType:	'json',
					data:		{
                        page_id    : page_id,
                        del_js_file: $(this).parent().prop('id'),
                        '_cat_ajax': 1
                    },
					cache:		false,
					beforeSend:	function( data )
					{
						data.process	= set_activity( 'Remove...' );
					},
					success:	function( data, textStatus, jqXHR  )
					{
                        $('.popup').dialog('destroy').remove();
                        if ( data.success === true )
						{
							location.reload(true);
						}
						else {
							return_error( jqXHR.process , data.message );
						}
                    }
                });
        });
    }

    if($('#fc_pages_headerfiles_css').length) {

        // add CSS
        $('.fcAddCSS').unbind('click').click(function(e) {
            e.preventDefault();
            var page_id = $('#fc_headers_pageid').text();
            if(page_id === '') {
                page_id = 0;
            }
            $('div#dlgAddCSS').dialog({
                modal: true
                ,width: 600
                ,height: 150
                ,buttons: [
                    {
                        text: cattranslate("Save"),
                        click: function() {
                            $(this).dialog("close");
                            $.ajax(
            				{
            					type:		'POST',
            					url:		CAT_ADMIN_URL + '/pages/ajax_headers.php',
            					dataType:	'json',
            					data:		{
                                    page_id    : page_id,
                                    add_css_file: $('div#dlgAddCSS').find('select:first').val(),
                                    '_cat_ajax': 1
                                },
            					cache:		false,
            					beforeSend:	function( data )
            					{
            						data.process	= set_activity( 'Adding...' );
            					},
            					success:	function( data, textStatus, jqXHR  )
            					{
                                    $('.popup').dialog('destroy').remove();
                                    if ( data.success === true )
            						{
            							location.reload(true);
            						}
            						else {
            							return_error( jqXHR.process , data.message );
            						}
                                }
                            });
                        }
                    }, {
                        text: cattranslate("Close"),
                        click: function() {
                            $(this).dialog("close");
                        }
                    }
                ]
            });
        });

        // remove CSS
        $('.fcDelCSS').unbind('click').click(function(e) {
            var page_id = $('#fc_headers_pageid').text();
            if(page_id === '') {
                page_id = 0;
            }
            $.ajax(
				{
					type:		'POST',
					url:		CAT_ADMIN_URL + '/pages/ajax_headers.php',
					dataType:	'json',
					data:		{
                        page_id    : page_id,
                        del_css_file: $(this).parent().prop('id'),
                        '_cat_ajax': 1
                    },
					cache:		false,
					beforeSend:	function( data )
					{
						data.process	= set_activity( 'Remove...' );
					},
					success:	function( data, textStatus, jqXHR  )
					{
                        $('.popup').dialog('destroy').remove();
                        if ( data.success === true )
						{
							location.reload(true);
						}
						else {
							return_error( jqXHR.process , data.message );
						}
                    }
                });
        });
    }
});