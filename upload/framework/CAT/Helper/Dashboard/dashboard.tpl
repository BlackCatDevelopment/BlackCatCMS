{% if addable %}
<div style="width:100%;text-align:right;">
    <form>
        <input type="hidden" name="dashboard_add_widget_module" id="dashboard_add_widget_module" value="{{module}}" />
        <label for="dashboard_add_widget">{{translate('Add widget')}}</label>
        <select name="dashboard_add_widget" id="dashboard_add_widget">
            {% for item in addable %}
            <option value="{{item.path}}">{{item.title}}</option>
            {% endfor %}
        </select>
        <button id="dashboard_add_widget_submit">{{translate('Insert')}}</button>
        <button id="dashboard_reset">{{translate('Reset Dashboard')}}</button>
    </form>
</div>
{% endif %}

{% if not dashboard %}
<div class="fc_info" style="margin:15px 10px">
    {{translate('This is your dashboard, but it\'s empty because you do not have superuser permissions. Use the page tree to edit pages. Use the top links to navigate through the backend.')}}
</div>
{% else %}
{% for col in dashboard.columns %}
    <ul style="width:{{col.width}}%;" class="fc_widgets fc_dashboard_sortable" id="dashboard_col_{{loop.index0}}" data-module="{{module}}">
    {% for key, widget in col.widgets %}
        <li class="fc_widget_wrapper clearfix" id="dashboard_col_{{loop.parent.loop.index0}}_item_{{loop.index0}}" data-widget="{{widget.widget_path}}">
            <div class="fc_widget_title">
                <div class="fc_widget_top">
                    <div data-action="close" data-widget="{{widget.widget_path}}" class="icon icon-eye{% if widget.isMinimized %}-blocked{% endif %}"></div>
                    <div data-action="remove" data-widget="{{widget.widget_path}}" class="icon icon-remove"></div>
                </div>
                {{widget.settings.widget_title}}
            </div>
			<div class="fc_widget_content">{{widget.content|raw}}</div>
		</li>
    {% endfor %}
    </ul>
{% endfor %}
{% endif %}
