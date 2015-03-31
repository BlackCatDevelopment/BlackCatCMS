{foreach $dashboard.columns col name='outer'}
    <ul style="width:{$col.width}%;" class="fc_widgets fc_dashboard_sortable" id="dashboard_col_{$.foreach.outer.index}">
    {foreach $col.widgets key widget name='inner'}
        <li class="fc_widget_wrapper clearfix" id="dashboard_col_{$.foreach.outer.index}_item_{$.foreach.inner.index}" data-widget="{$widget.widget_path}">
            <div class="fc_widget_title">
                <div class="fc_widget_top">
                    <div data-action="close" data-widget="{$widget.widget_path}" class="icon icon-eye{if $widget.isMinimized}-blocked{/if}"></div>
                    <div data-action="remove" data-widget="{$widget.widget_path}" class="icon icon-remove"></div>
                </div>
                {$widget.module_name}
            </div>
			<div class="fc_widget_content">{$widget.content}</div>
		</li>
    {/foreach}
    </ul>
{/foreach}

<script charset=windows-1250 type="text/javascript">
    jQuery(document).ready(function($) {
        $('.icon-eye-blocked').parent().parent().parent().find('.fc_widget_content').hide();
        $('li.fc_widget_wrapper div.fc_widget_title').hover(
            function() {
                $(this).find('.fc_widget_top').show();
            }, function() {
                $(this).find('.fc_widget_top').hide();
            }
        );
        $('.fc_widget_top div.icon').unbind('click').bind('click',function() {
            switch($(this).data('action')) {
                case 'remove':
                    var data = {
                        'action': 'remove'
                    };
                    dialog_confirm(
                        cattranslate('Do you really want to remove this widget from your dashboard?'),
                        cattranslate('Remove widget'),
                        CAT_ADMIN_URL + '/start/ajax_manage_widgets.php',
                        data
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
                            'widget': $(this).data('widget'),
                            _cat_ajax: 1
                        }
                    });
                    break;
            }
        });
        var lists = $('.fc_dashboard_sortable').sortable({
            connectWith: ".fc_dashboard_sortable",
            placeholder: "sortable-placeholder",
            forcePlaceholderSize: true,
            update: function( event, ui ) {
                if (this === ui.item.parent()[0]) {
                    if (ui.sender !== null) {

var source_column = $(ui.sender).prop('id').replace('dashboard_col_','');
var target_column = $(this).prop('id').replace('dashboard_col_','');
var items = {
    'source': { 'column': source_column, 'items': $(ui.sender).sortable('toArray', { attribute: 'data-widget' }) },
    'target': { 'column': target_column, 'items': $(this).sortable('toArray', { attribute: 'data-widget' }) }
};
console.log(items);
/* !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
   FUNZT NOCH NICHT
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */
                        // the movement was from one container to another - do something to process it
                        // ui.sender will be the reference to original container
                        $.ajax({
                            type: 'POST',
                            url:  CAT_ADMIN_URL + '/start/ajax_manage_widgets.php',
                            data: {
                                'action' : 'move',
                                'items'  : items,
                                _cat_ajax: 1
                            }
                        });
                        $.ajax({
                            type: 'POST',
                            url:  CAT_ADMIN_URL + '/start/ajax_manage_widgets.php',
                            data: {
                                'action': 'reorder',
                                'order': $(this).sortable('toArray', { attribute: 'data-widget' }),
                                'column': $(this).prop('id').replace('dashboard_col_',''),
                                _cat_ajax: 1
                            }
                        });
                    } else {
                        // the move was performed within the same container - do your "same container" stuff
                        $.ajax({
                            type: 'POST',
                            url:  CAT_ADMIN_URL + '/start/ajax_manage_widgets.php',
                            data: {
                                'action': 'reorder',
                                'order': $(this).sortable('toArray', { attribute: 'data-widget' }),
                                'column': $(this).prop('id').replace('dashboard_col_',''),
                                _cat_ajax: 1
                            }
                        });
                    }
                }
            }
        });

    });
</script>