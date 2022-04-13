		<div id="fc_list_{% if addon.directory %}{{addon.directory}}{% else %}{{addon.INSTALL.directory}}{% endif %}" class="fc_list_forms fc_form_content">

{% if addon.is_removable and addon.is_installed and permissions.MODULES_UNINSTALL %}
			<form name="uninstall" action="uninstall.php" method="post" class="submit_settings fc_gradient1">
                <input type="hidden" name="{{token_name}}" value="{{token}}">
				<input type="hidden" name="file" value="{{addon.directory}}">
				<input type="hidden" name="type" value="{{addon.type}}">
				<strong>{{translate('Module details')}}: {{addon.name}}</strong>
				<input type="submit" name="uninstall_module" value="{{translate('Uninstall Addon')}}" class="fc_gradient_red">
			</form>
{% else %}
            <div class="submit_settings fc_gradient1">
                <strong>{{translate('Module details')}}: {{addon.name}}</strong>
                {% if not addon.is_removable %}
                <span>{{translate('Marked as mandatory')}}</span>
                {% endif %}
            </div>
{% endif %}
			<div class="clear_sp"></div>

{% if addon.description or addon.type == 'languages' %}
			{% if addon.description %}
    			<div>
    				{% if addon.icon %}<img class="right" src="{{addon.icon}}" alt="{{addon.name}}">{% endif %}
    				{{addon.description}}
    				<div class="clear"></div>
    			</div>
    			<div class="clear"></div>
    			<hr>
			{% endif %}

        {% if usage|length>0 %}
            <div style="float:right;">
                {{translate('This module is used on the following pages')}}:<br>
                {% for item in usage %}
                <a href="{{item.page_link}}">{{item.menu_title}}</a><br>
                {% endfor %}
            </div>
        {% endif %}

			<p>
			<span class="fc_label_200">{{translate('Version')}}:</span>{{addon.version}}<br>
			<span class="fc_label_200">{{translate('Author')}}:</span>{{addon.author}}<br>
            {% if addon.link %}<span class="fc_label_200">{{translate('Link')}}:</span><a href="{{addon.link}}" target="_blank">{{addon.link}}</a><br>{% endif %}
			{% if addon.function %}<span class="fc_label_200">{{translate('Function')}}:</span>{{addon.function}}<br>{% endif %}
			<span class="fc_label_200">{{translate('Designed for')}}:</span>{% if addon.cms_name %}{{addon.cms_name}}{% else %}BlackCat CMS{% endif %} {{addon.platform}}<br>
			<span class="fc_label_200">{{translate('License')}}:</span>{{addon.license}}<br>
            {% if addon.installed %}<span class="fc_label_200">{{translate('Installed')}}:</span>{{addon.installed}}<br>{% endif %}
            {% if addon.upgraded %}<span class="fc_label_200">{{translate('Upgraded')}}:</span>{{addon.upgraded}}<br>{% endif %}
			</p>
			{% if permissions.MODULES_UNINSTALL and not addon.UNINSTALLED %}
    			<div class="clear"></div>
    			<hr>
    			<div class="clear_sp"></div>
			{% endif %}
        <br style="clear:right">
{% endif %}

{% if permissions.MODULES_INSTALL and not addon.is_installed %}
            <h2>{{translate('Module seems to be not installed yet.')}}</h2>
{% endif %}

{% if permissions.MODULES_INSTALL %}
              {% if addon.type == 'module' %}
                  <p class="fc_gradient_red">{{translate('DANGER ZONE! This may delete your current data!')}}</p>
    			  <p>{{translate('When modules are uploaded via FTP (not recommended), the module installation functions install, upgrade or uninstall will not be executed automatically. Those modules may not work correct or do not uninstall properly.')}}<br>
                  {{translate('You can execute the module functions manually for modules uploaded via FTP below.')}}
                  </p>
              {% endif %}

              {% if addon.type == 'template' or addon.type == 'language' or addon.INSTALL %}
                  <form name="install" action="manual_install.php" method="post" style="float:left;">
    				<input type="hidden" name="action" value="install">
                    <input type="hidden" name="file" value="{% if addon.directory %}{{addon.directory}}{% else %}{{addon.INSTALL.directory}}{% endif %}">
                    <input type="hidden" name="type" value="{{addon.type}}">
    				<input type="submit" name="install_manual_module" class="fc_gradient_red" value="{{translate('Install manually')}}">
    			  </form>
              {% endif %}

              {% if addon.type == 'module' and not addon.INSTALL %}
                  <h3>{{translate('No install.php found! The module cannot be installed!')}}</h3>
              {% endif %}
{% endif %}

{% if permissions.MODULES_INSTALL and addon.UPGRADE and addon.is_installed %}
			<form name="upgrade" action="manual_install.php" method="post">
				<input type="hidden" name="action" value="upgrade">
                <input type="hidden" name="type" value="{{addon.type}}">
				<input type="hidden" name="file" value="{% if addon.directory %}{{addon.directory}}{% else %}{{addon.INSTALL.directory}}{% endif %}">
				<input type="submit" name="upgrade_module" class="fc_gradient_red" value="{{translate('Execute upgrade.php manually')}}">
			</form>
{% endif %}

			<div class="clear_sp"></div>
		</div>




