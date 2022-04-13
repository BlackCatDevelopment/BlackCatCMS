<div id="fc_content_header">
    {{translate('Addons')}}
</div>
<div id="fc_main_content">
    <div id="fc_lists_overview" class="fc_addons">
        <div id="fc_list_search">
            <div class="fc_input_fake">
                <input type="text" name="fc_list_search" id="fc_list_search_input" value="{{translate('Search...')}}">
                <label class="fc_close" for="fc_list_search_input"></label>
            </div>
        </div>
        <div class="fc_gradient1 fc_border">
            <button class="icon-puzzle fc_active fc_gradient1 fc_gradient_hover" title="{{translate('Modules')}}"></button>
            <button class="icon-color-palette fc_gradient1 fc_gradient_hover" title="{{translate('Templates')}}"></button>
            <button class="icon-comments fc_gradient1 fc_gradient_hover" title="{{translate('Languages')}}"></button>
            <button class="icon-folder-add fc_gradient1 fc_gradient_hover" title="{{translate('Not installed yet')}}"></button>
            {% if permissions.MODULES_INSTALL %}<button id="fc_list_add" class="icon-plus fc_gradient1 fc_gradient_hover" title="{{translate('Install Addon')}}"></button>{% endif %}
            <div class="clear"></div>
        </div>
        <ul id="fc_list_overview" class="fc_group_list">
        {% for addon in addons %}
            {% if addon.name %}
            <li class="fc_module_item fc_type_{{addon.type}}s fc_border fc_gradient1 fc_gradient_hover{% if addon.bundled == 'Y' %} fc_isbundled{% endif %}{% if addon.is_installed is same as false %} fc_not_installed{% endif %}">
                {% if addon.icon %}<img src="{{addon.icon}}" alt="{{addon.directory}}">
                {% elseif addon.type == 'templates' %}<span class="icon-color-palette"></span>{% elseif addon.type == 'languages' %}<span class="icon-comments"></span>{% else %}<span class="icon-puzzle"></span>{% endif %}
                <span class="fc_groups_name"> {{addon.name}}</span>
                <input type="hidden" name="addon_directory" value="{{addon.directory}}">
                <input type="hidden" name="addon_type" value="{{addon.type}}">
            </li>
            {% else %}
            <li class="fc_uninstalled_addon">
                <span class="fc_groups_name">{{addon.INSTALL.name}}</span>
            </li>
            {% endif %}
            {% endfor %}
            {% if not_installed_addons %}
            <li class="fc_border fc_gradient4 fc_not_installed fc_type_heading">
                <span class="fc_groups_name">{{translate('Not installed yet')}}</span>
            </li>
{% for type, addons in not_installed_addons %}
    {% for addon in addons %}
            {% if addon.name %}
            <li class="fc_module_item fc_border fc_gradient1 fc_gradient_hover fc_type_{{type}} fc_not_installed">
                {% if addon.icon %}<img src="{{addon.icon}}" alt="{{addon.directory}}">
                {% else %}<span class="icon-puzzle"></span>{% endif %}
                <span class="fc_groups_name"> {{addon.name}}</span>
                <input type="hidden" name="addon_directory" value="{{addon.directory}}">
                <input type="hidden" name="addon_type" value="{{addon.type}}">
            </li>
            {% else %}
            <li class="fc_uninstalled_addon">
                <span class="fc_groups_name">{{addon.INSTALL.name}}</span>
            </li>
            {% endif %}
    {% endfor %}
{% endfor %}
            {% endif %}
        </ul>
    </div>

    <div class="fc_all_forms">
        {% if permissions.MODULES_INSTALL %}
        <ul class="tabs primary-nav fc_gradient4">
            <li class="tabs__item" id="tab_item_1">
                <a href="#" class="tabs__link current" id="fc_addons_upload">{{translate('Upload and install')}}</a>
            </li>
            <li class="tabs__item" id="tab_item_2">
                <a href="#" class="tabs__link" id="fc_addons_catalog">{{translate('Show Catalog')}}</a>
            </li>
            <li class="tabs__item" id="tab_item_3">
                <a href="#" class="tabs__link" id="fc_addons_create">{{translate('Create new')}}</a>
            </li>
        </ul>
        {% endif %}

        <div id="addons_main_content">
        {% if permissions.MODULES_INSTALL %}
        {% include 'backend_addons_index_upload.tpl' %}
        {% else %}
        {{translate('Click on an item in the list to see the details.')}}
        {% endif %}
        </div>
    </div>
</div>