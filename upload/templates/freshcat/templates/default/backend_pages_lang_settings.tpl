{% include 'backend_pages_header.tpl' %}
<div id="fc_main_content">
	<div class="fc_modified_header">
		<div class="fc_current_page">
			{{translate('Current page')}}: <strong>{{PAGE_TITLE}}</strong> (<strong>ID: {{PAGE_ID}}</strong>)
		</div>
		<div class="fc_modified">
			{% if MODIFIED_WHEN %}
			{{translate('Last modification by')}} {{MODIFIED_BY}} ({{MODIFIED_BY_USERNAME}}), {{MODIFIED_WHEN}}
			{% endif %}
		</div>
		<div class="clear"></div>
	</div>

    <div id="fc_linked_languages" class="ui-corner-bottom" style="padding:5px 15px">
        <div class="fc_info">
        {{translate('Language mappings allow to link pages of different languages together. In combination with the <tt>language_menu()</tt> function in the template, you will get links to all available languages for a page.')}}
        </div>
    </div>
    <div class="clear sp_clear"></div>

    <div class="row">
        <div class="six columns fc_form_content ui-shadow ui-corner-all">
            <h2 style="margin:0" class="fc_modified_header">{{translate('Current links')}}</h2>
            {% if PAGE_LINKS|length > 0 %}
            <table class="fc_table fc_gradient2 fc_border" style="width:100%">
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>{{translate('Language')}}</th>
                        <th>{{translate('Page')}}</th>
                    </tr>
                </thead>
                <tbody>
                {% for link in PAGE_LINKS %}
                    <tr>
                        <td><a href="{{CAT_ADMIN_URL}}/pages/lang_settings.php?page_id={{PAGE_ID}}&amp;del={{link.lang}}_{{link.page_id}}"><img src="{{CAT_THEME_URL}}/images/delete_16.png" border="0" alt="X" /></a></td>
                        <td>{{link.lang}}</td>
                        <td>{{link.menu_title}}</td>
                    </tr>
                {% endfor %}
                </tbody>
            </table>
            {% else %}
            <div class="fc_header highlight">{{translate('No current links')}}</div>
            {% endif %}
        </div>

        <div class="six columns fc_form_content ui-shadow ui-corner-all">
        <h2 style="margin:0" class="fc_modified_header">{{translate('Create link')}}</h2>
        {% if AVAILABLE_PAGES|length > 0 %}
        {% if AVAILABLE_LANGS|length > 0 %}
    	<form name="lang_settings" action="{{CAT_ADMIN_URL}}/pages/lang_settings_save.php" method="post" class="fc_gradient1">
    		<input type="hidden" name="page_id" value="{{PAGE_ID}}" />

            <label for="fc_map_language" class="fc_label_120">{{translate('Map to language')}}</label>
            <select id="fc_map_language" name="map_language">
            {% for lang in AVAILABLE_LANGS %}
                <option value="{{lang.VALUE}}">{{lang.NAME}}</li>
            {% endfor %}
            </select><br />

            <label for="fc_link_page_id" class="fc_label_120">{{translate('Map to page')}}</label>
            <select id="fc_link_page_id" name="link_page_id">
            {{PAGES}}
            </select>

    		<div class="fc_confirm_bar ui-corner-bottom">
    			<input type="submit" name="submit" value="{{translate('Save')}}" />
    			<input type="reset" name="reset" value="{{translate('Reset')}}" />
    		</div>
    	</form>
        {% else %}
        <div class="fc_header highlight">{{translate('No more languages available')}}</div>
        {% endif %}
      {% else %}
        <div class="fc_header highlight">{{translate('No pages available')}}</div>
        {% endif %}
        </div>
    </div>
</div>