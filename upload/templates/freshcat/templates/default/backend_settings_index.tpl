<div id="fc_content_header">
	{translate('Settings')}
</div>
<div id="fc_main_content">
	<form name="settings" id="settings" action="{$CAT_ADMIN_URL}/settings/ajax_save_settings.php" method="post" class="ajax_form">
    <input type="hidden" name="_cat_ajax" value="true" />
 	<div id="fc_lists_overview">
		<div id="fc_list_search" class="fc_gradient1">
			<div class="fc_input_fake">
				<input type="text" name="fc_list_search" id="fc_list_search_input" value="{translate('Search...')}" />
				<label class="fc_close" for="fc_list_search_input"></label>
			</div>
		</div>
		<ul id="fc_list_overview" class="fc_group_list fc_settings_list">
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-binocular">
				<span class="fc_groups_name">{translate('SEO settings')}</span>
				<input type="hidden" name="rel" value="seo" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-screen-2">
				<span class="fc_groups_name">{translate('Frontend settings')}</span>
				<input type="hidden" name="rel" value="frontend" />
			</li>
            <li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-globe">
				<span class="fc_groups_name">{translate('Global headers')}</span>
				<input type="hidden" name="rel" value="headers" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-equalizer">
				<span class="fc_groups_name">{translate('Backend settings')}</span>
				<input type="hidden" name="rel" value="backend" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-cog">
				<span class="fc_groups_name">{translate('System settings')}</span>
				<input type="hidden" name="rel" value="system" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-users">
				<span class="fc_groups_name">{translate('User settings')}</span>
				<input type="hidden" name="rel" value="users" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-alarm">
				<span class="fc_groups_name">{translate('Language & time')}</span>
				<input type="hidden" name="rel" value="datetime" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-search">
				<span class="fc_groups_name">{translate('Search settings')}</span>
				<input type="hidden" name="rel" value="searchblock" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-database">
				<span class="fc_groups_name">{translate('Server settings')}</span>
				<input type="hidden" name="rel" value="server" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-mail">
				<span class="fc_groups_name">{translate('Mailer settings')}</span>
				<input type="hidden" name="rel" value="mail" />
			</li>
            <li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-shield">
				<span class="fc_groups_name">{translate('Security settings')}</span>
				<input type="hidden" name="rel" value="security" />
			</li>
            <li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-info">
				<span class="fc_groups_name">{translate('System information')}</span>
				<input type="hidden" name="rel" value="sysinfo" />
			</li>
		</ul>
	</div>
	<p class="submit_settings fc_gradient1">
		<input type="submit" name="submit" value="{translate('Save')}" />
		<input type="reset" name="reset" value="{translate('Reset')}" />
	</p>
	<div class="fc_gradient1 fc_all_forms">
        <input type="hidden" name="current_page" id="current_page" value="seo" />
        <div id="fc_set_form_content" class="fc_gradient1 ui-corner-top">
            {$INDEX}
		</div>
    </div>
    <p class="submit_settings fc_gradient1">
		<input type="submit" name="submit" id="submit_settings_bottom" value="{translate('Save')}" />
		<input type="reset" name="reset" value="{translate('Reset')}" />
	</p>
</form><!-- settings -->
{*<script src="{$CAT_ADMIN_URL}/settings/setting.js" type="text/javascript"></script>*}
</div>