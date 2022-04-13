    <script charset=windows-1250 type="text/javascript">
        $.getScript( "{{CAT_URL}}/templates/freshcat/js/backend_headerfiles.js" );
    </script>

    <div class="fc_settings_max left" style="margin-left:15px;">
        <div class="fc_settings_label" id="fc_page_down">
            <input type="checkbox" class="fc_checkbox_jq" name="use_core" id="fc_use_core" value="on" {% if use_core == 'Y' %} checked="checked"{% endif %}>
            <label for="fc_use_core" title="{{translate('The jQuery Core will be added automatically if any jQuery Plugin is activated.')}}">{{translate('Use jQuery')}}</label>
        </div>

        <div class="fc_settings_label" id="fc_page_down">
            <input type="checkbox" class="fc_checkbox_jq" name="use_ui" id="fc_use_ui" value="on" {% if use_ui == 'Y' %} checked="checked"{% endif %}>
            <label for="fc_use_ui" title="{{translate('If you wish to use UI components like Accordion or Tabs, check this.')}}">{{translate('Use jQuery UI')}}</label>
        </div>
    </div>

    <div class="fc_text_right right">
        <button class="fcAdd fcAddPlugin fc_gradient_blue fc_br_all icon icon-plus">
            {{translate('Add jQuery Plugin')}}
        </button>
        <button class="fcAdd fcDelPlugin fc_gradient_blue fc_br_all icon icon-minus">
            {{translate('Remove jQuery Plugin')}}
        </button>
    </div><br style="clear:both;">

    <ul id="fc_pages_headerfiles_js">
        <li class="fc_module_block">
            <div class="fc_module_content fc_shadow_small fc_br_all">
                <div class="fc_blocks_header fc_gradient1 fc_border fc_br_top fc_section_header_block">
                    <strong>{{translate('Javascript files')}}</strong>
                </div>
                <div class="fc_blocks_content">
                    <button id="fcAddJS" class="fcAdd right fc_gradient_blue fc_br_all icon icon-plus">
                    {{translate('Add')}}
                    </button>
                {% if page_js and page_js is iterable %}
                    <ul class="ui-sortable">
                    {%for file in page_js %}
                        <li id="{{file}}" class="page_js">
                            <button class="fcDel fcDelJS icon icon-minus fc_br_all fc_gradient2" style="padding:0;line-height:1em;margin-right:10px;"></button>
                            <span class="page_js" title="{{file}}">{{file}}</span>
                        </li>
                    {% endfor %}
                    <ul>
                {% else %}
                    {{translate('Currently, no extra files are defined')}}
                {% endif %}
                </div>
            </div>
        </li>
    </ul>

    <ul id="fc_pages_headerfiles_css">
        <li class="fc_module_block">
            <div class="fc_module_content fc_shadow_small fc_br_all">
                <div class="fc_blocks_header fc_gradient1 fc_border fc_br_top fc_section_header_block">
                    <strong>{{translate('CSS files')}}</strong>
                </div>
                <div class="fc_blocks_content">
                    <button class="fcAdd fcAddCSS right fc_gradient_blue fc_br_all icon icon-plus">
                    {{translate('Add')}}
                    </button>
                {% if page_css and page_css is iterable %}
                    <ul class="ui-sortable">
                    {% for file in page_css %}
                        <li id="{{file}}" class="page_css">
                            <button class="fcDel fcDelCSS icon icon-minus fc_br_all fc_gradient2" style="padding:0;line-height:1em;margin-right:10px;"></button>
                            <span class="page_css" title="{{file}}">{{file}}</span>
                        </li>
                    {% endfor %}
                    <ul>
                {% else %}
                    {{translate('Currently, no extra files are defined')}}
                {% endif %}
                </div>
            </div>
        </li>
    </ul>

    <div id="dlgAddPlugin" style="display:none;" title="{{translate('Add jQuery Plugin')}}">
        {{translate('Please choose')}}
        <select name="">
        {% for dir in jquery_plugins %}<option value="{{dir}}">{{dir}}</option>{% endfor %}
        </select><br><br>
        <span class="warning fc_br_all">
        {{translate('Please note: By default, all *.js and *.css files in the plugin\'s folder are added to the list. You may have to remove some in the next step.')}}
        </span>
    </div>

    <div id="dlgAddJS" style="display:none;">
        {{translate('Please select')}}
        <select name="">
        {% for dir in js_files %}<option value="{{dir}}">{{dir}}</option>{% endfor %}
        </select>
    </div>

    <div id="dlgAddCSS" style="display:none;">
        {{translate('Please select')}}
        <select name="">
        {% if css_files %}
        <optgroup label="{{translate('jQuery Plugins')}}">
        {% for dir in css_files %}<option value="{{dir}}">{{dir}}</option>{% endfor %}
        </optgroup>
        {% endif %}
        {% if wysiwyg_files %}
        <optgroup label="{{translate('WYSIWYG Editor Plugins')}}">
        {% for dir in wysiwyg_files %}<option value="{{dir}}">{{dir}}</option>{% endfor %}
        </optgroup>
        {% endif %}
        </select>
    </div>
