{if $addable}
<div style="width:100%;text-align:right;">
    <form>
        <input type="hidden" name="dashboard_add_widget_module" id="dashboard_add_widget_module" value="{$module}" />
        <label for="dashboard_add_widget">{translate('Add widget')}</label>
        <select name="dashboard_add_widget" id="dashboard_add_widget">
            {foreach $addable item}
            <option value="{$item.path}">{$item.title}</option>
            {/foreach}
        </select>
        <button id="dashboard_add_widget_submit">{translate('Insert')}</button>
        <button id="dashboard_reset">{translate('Reset Dashboard')}</button>
    </form>
</div>
{/if}

{if ! $dashboard}
<div class="fc_info" style="margin:15px 10px">
    {translate('This is your dashboard, but it\'s empty because you do not have superuser permissions. Use the page tree to edit pages. Use the top links to navigate through the backend.')}
</div>
{else}
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
{/if}
