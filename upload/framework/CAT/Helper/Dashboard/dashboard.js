jQuery(document).ready(function($) {
    var afterSend = function( data, textStatus, jqXHR ) { location.reload(); };
    // get the module name; defaults to 'backend' (global dashboard)
    var module = 'backend';
    if( typeof $('ul.fc_widgets').data('module') != 'undefined' && $('ul.fc_widgets').data('module').length) {
        module = $('ul.fc_widgets').data('module');
    }
    // make sure we have some space to drop elements on empty cols
    $('ul.fc_widgets').not(':has(li)').addClass('empty');
    // hide all minimized widgets
    $('.icon-eye-blocked').parent().parent().parent().find('.fc_widget_content').hide();
    // add hover icons
    $('li.fc_widget_wrapper div.fc_widget_title').hover(
        function() {
            $(this).find('.fc_widget_top').show();
        }, function() {
            $(this).find('.fc_widget_top').hide();
        }
    );
    // icon click
    $('.fc_widget_top div.icon').unbind('click').bind('click',function() {
        switch($(this).data('action')) {
            case 'remove':
                var data = {
                    'action': 'remove',
                    'module': module,
                    'widget': $(this).data('widget')
                };
                var undf;
                dialog_confirm(
                    cattranslate('Do you really want to remove this widget from your dashboard?'),
                    cattranslate('Remove widget'),
                    CAT_ADMIN_URL + '/start/ajax_manage_widgets.php',
                    data,
                    'POST',
                    'JSON',
                    undf,
                    afterSend
                );
                break;
            case 'close':
                $(this).parent().parent().parent().find('.fc_widget_content').slideToggle(300);
                $(this).toggleClass('icon-eye').toggleClass('icon-eye-blocked');
                $.ajax({
                    type: 'POST',
                    url:  CAT_ADMIN_URL + '/start/ajax_manage_widgets.php',
                    data: {
                        'action': ( $(this).hasClass('icon-eye-blocked')?'hide':'show' ),
                        'module': module,
                        'widget': $(this).data('widget'),
                        _cat_ajax: 1
                    }
                });
                break;
        }
    });
    // add widget
    $('#dashboard_add_widget_submit').unbind('click').bind('click',function(event) {
        event.preventDefault();
        var data = {
            'action' : 'add',
            'module' : $('input#dashboard_add_widget_module').val(),
            'widget' : $('select#dashboard_add_widget').val(),
            _cat_ajax: 1
        };
        $.ajax({
            type: 'POST',
            url : CAT_ADMIN_URL + '/start/ajax_manage_widgets.php',
            data   : data,
            success: afterSend
        });
    });
    // manage widgets (move, reorder)
    var lists = $('.fc_dashboard_sortable').sortable({
        connectWith: ".fc_dashboard_sortable",
        placeholder: "sortable-placeholder",
        forcePlaceholderSize: true,
        dropOnEmpty: true,
        update: function( event, ui ) {
            if (this === ui.item.parent()[0]) {
                if (ui.sender !== null) {
                    var source_column = $(ui.sender).prop('id').replace('dashboard_col_','');
                    var target_column = $(this).prop('id').replace('dashboard_col_','');
                    // we need the items of the target column for correct sort order
                    var items = {
                        'source': { 'column': source_column },
                        'target': { 'column': target_column, 'items': $(this).sortable('toArray', { attribute: 'data-widget' }) }
                    };
                    // the movement was from one container to another - do something to process it
                    // ui.sender will be the reference to original container
                    $.ajax({
                        type: 'POST',
                        url:  CAT_ADMIN_URL + '/start/ajax_manage_widgets.php',
                        data: {
                            'action' : 'move',
                            'module' : module,
                            'items'  : items,
                            'widget' : $(ui.item).data('widget'),
                            _cat_ajax: 1
                        }
                    });
                } else {
                    // the move was performed within the same container - do your "same container" stuff
                    $.ajax({
                        type: 'POST',
                        url:  CAT_ADMIN_URL + '/start/ajax_manage_widgets.php',
                        data: {
                            'action' : 'reorder',
                            'module' : module,
                            'order'  : $(this).sortable('toArray', { attribute: 'data-widget' }),
                            'column' : $(this).prop('id').replace('dashboard_col_',''),
                            _cat_ajax: 1
                        }
                    });
                }
            }
            $(this).removeClass('empty');
        }
    });
    // reset dashboard
    $('#dashboard_reset').unbind('click').bind('click',function(event) {
        event.preventDefault();
        var data = {
            'action': 'reset',
            'module': module
        };
        var undf;
        dialog_confirm(
            cattranslate('Do you really want to reset your dashboard? This will delete all your settings!'),
            cattranslate('Reset Dashboard'),
            CAT_ADMIN_URL + '/start/ajax_manage_widgets.php',
            data,
            'POST',
            'JSON',
            undf,
            afterSend
        );
    });
});
