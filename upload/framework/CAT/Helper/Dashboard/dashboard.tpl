{foreach $dashboard.columns col name='outer'}
    <ul style="width:{$col.width}%;" class="fc_widgets fc_dashboard_sortable" id="dashboard_col_{$.foreach.outer.index}" data-module="{$module}">
    {foreach $col.widgets key widget name='inner'}
        <li class="fc_widget_wrapper clearfix" id="dashboard_col_{$.foreach.outer.index}_item_{$.foreach.inner.index}" data-widget="{$widget.widget_path}">
            <div class="fc_widget_title">
                <div class="fc_widget_top">
                    <div data-action="close" data-widget="{$widget.widget_path}" class="icon icon-eye{if $widget.isMinimized}-blocked{/if}"></div>
                    <div data-action="remove" data-widget="{$widget.widget_path}" class="icon icon-remove"></div>
                </div>
                {$widget.settings.widget_title}
            </div>
			<div class="fc_widget_content">{$widget.content}</div>
		</li>
    {/foreach}
    </ul>
{/foreach}
