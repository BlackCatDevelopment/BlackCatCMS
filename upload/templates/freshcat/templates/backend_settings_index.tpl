<div id="fc_content_header">
	{translate('Settings')}
</div>
<div id="fc_main_content">
	<form name="settings" action="{$CAT_ADMIN_URL}/settings/save.php" method="post">
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
				<input type="hidden" name="rel" value="SEO" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-screen-2">
				<span class="fc_groups_name">{translate('Frontend settings')}</span>
				<input type="hidden" name="rel" value="FRONTEND" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-equalizer">
				<span class="fc_groups_name">{translate('Backend settings')}</span>
				<input type="hidden" name="rel" value="BACKEND" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-cog">
				<span class="fc_groups_name">{translate('System settings')}</span>
				<input type="hidden" name="rel" value="SYSTEM" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-users">
				<span class="fc_groups_name">{translate('User settings')}</span>
				<input type="hidden" name="rel" value="USERS" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-alarm">
				<span class="fc_groups_name">{translate('Language & time')}</span>
				<input type="hidden" name="rel" value="TIME_LANGUAGE" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-search">
				<span class="fc_groups_name">{translate('Search settings')}</span>
				<input type="hidden" name="rel" value="SEARCHING" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-database">
				<span class="fc_groups_name">{translate('Server settings')}</span>
				<input type="hidden" name="rel" value="SERVER" />
			</li>
			<li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-mail">
				<span class="fc_groups_name">{translate('Mailer settings')}</span>
				<input type="hidden" name="rel" value="MAIL" />
			</li>
            <li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-shield">
				<span class="fc_groups_name">{translate('Security settings')}</span>
				<input type="hidden" name="rel" value="SEC" />
			</li>
            <li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-info">
				<span class="fc_groups_name">{translate('System information')}</span>
				<input type="hidden" name="rel" value="SYSINFO" />
			</li>
		</ul>
	</div>
	<p class="submit_settings fc_gradient1">
		<input type="submit" name="submit" value="{translate('Save')}" />
		<input type="reset" name="reset" value="{translate('Reset')}" />
	</p>
	<div class="fc_gradient1 fc_all_forms">
		<div id="fc_list_SEO" class="fc_gradient1 fc_list_forms ui-corner-top">
			<label class="fc_label_120" for="fc_website_title">{translate('Website title')}:</label>
			<input type="text" name="website_title" id="fc_website_title"  value="{$values.website_title}" />
			<hr />

			<label class="fc_label_120" for="fc_website_description">{translate('Website description')}:</label><br/>
			<textarea name="website_description" id="fc_website_description" cols="80" rows="6" >{$values.website_description}</textarea>
			<div class="clear_sp"></div>
			<label class="fc_label_120" for="fc_website_keywords">{translate('Website keywords')}:</label><br/>
			<textarea name="website_keywords" id="fc_website_keywords" cols="80" rows="6" >{$values.website_keywords}</textarea>
			<div class="clear_sp"></div>
			<p class="submit_settings fc_gradient1">
				<input type="submit" name="submit" value="{translate('Save')}" />
				<input type="reset" name="reset" value="{translate('Reset')}" />
			</p>
		</div><!-- SEO -->

		<div id="fc_list_FRONTEND" class="fc_gradient1 fc_list_forms ui-corner-top">
		{include backend_settings_index_frontend.tpl}
		</div><!-- FRONTEND -->


		<div id="fc_list_BACKEND" class="fc_gradient1 fc_list_forms ui-corner-top">
        {include backend_settings_index_backend.tpl}
		</div><!-- BACKEND -->


		<div id="fc_list_SYSTEM" class="fc_gradient1 fc_list_forms ui-corner-top">
        {include backend_settings_index_system.tpl}
		</div><!-- SYSTEM -->


		<div id="fc_list_USERS" class="fc_gradient1 fc_list_forms ui-corner-top">
        {include backend_settings_index_users.tpl}
		</div><!-- USERS -->
	
		<div id="fc_list_TIME_LANGUAGE" class="fc_gradient1 fc_list_forms ui-corner-top">
        {include backend_settings_index_datetime.tpl}
		</div><!-- TIME_LANGUAGE -->

		<div id="fc_list_SEARCHING" class="fc_gradient1 fc_list_forms ui-corner-top">
        {include backend_settings_index_searchblock.tpl}
		</div><!-- SEARCHING -->


		<div id="fc_list_SERVER" class="fc_gradient1 fc_list_forms ui-corner-top">
        {include backend_settings_index_server.tpl}
		</div><!-- SERVER -->



		<div id="fc_list_MAIL" class="fc_gradient1 fc_list_forms ui-corner-top">
			<p>{translate('Please specify a default "FROM" address and "SENDER" name below. It is recommended to use a FROM address like: <strong>admin@yourdomain.com</strong>. Some mail provider (e.g. <em>mail.com</em>) may reject mails with a FROM: address like <em>name@mail.com</em> sent via a foreign relay to avoid spam.<br /><br />The default values are only used if no other values are specified by Black Cat CMS. If your server supports <acronym title="Simple mail transfer protocol">SMTP</acronym>, you may want use this option for outgoing mails.')}
			</p>
			<div class="clear"></div>
			<hr />

            {if count($CATMAILER_LIBS)>1}
            <div id="fc_catmailer_lib" class="settings_label fc_settings_max">
                <span class="fc_label_200" for="fc_catmailer_lib">{translate('Mailer library')}:</span>
                {foreach $CATMAILER_LIBS item}
                    <input type="radio" class="fc_radio_jq" name="catmailer_lib" id="fc_catmailer_{$item.dir}" value="{$item.dir}" {if $values.catmailer_lib == $item.dir}checked="checked"{/if}/>
                    <label for="fc_catmailer_{$item.dir}">{$item.name}</label>
                {/foreach}
            </div>
            {/if}

			<div class="clear_sp"></div>
			<label class="fc_label_200" for="fc_list_SERVER_email">{translate('Default "from" mail')}:</label>
			<input type="text" name="server_email" id="fc_list_SERVER_email" value="{$values.server_email}" />
			<div class="clear"></div>

			<label class="fc_label_200" for="fc_catmailer_default_sendername">{translate('Default sender name')}:</label>
			<input type="text" name="catmailer_default_sendername" id="fc_catmailer_default_sendername" value="{$values.wb_default_sendername}" />
			<div class="clear_sp"></div>

            <div id="testmail_result" style="display:none;">&nbsp;</div>
			{if $DISPLAY_ADVANCED}
			<label class="fc_label_200" for="fc_catmailer_routine">{translate('Mail routine')}:</label>
			<div class="settings_label fc_settings_max" id="fc_catmailer_routine">
				<input type="radio" class="fc_radio_jq fc_toggle_element hide___fc_smtp" name="catmailer_routine" id="fc_catmailer_routine_phpmail" value="phpmail" {if $CATMAILER_ROUTINE == 'phpmail'} checked="checked"{/if}/>
				<label for="fc_catmailer_routine_phpmail">{translate('PHP mail')}</label>
				<input type="radio" class="fc_radio_jq fc_toggle_element show___fc_smtp" name="catmailer_routine" id="fc_catmailer_routine_smtp" value="smtp" {if $CATMAILER_ROUTINE == 'smtp'} checked="checked"{/if}/>
				<label for="fc_catmailer_routine_smtp">{translate('SMTP')}</label>
			</div>
			<div class="clear"></div>

			<div id="fc_smtp">
				<hr />
				<p>
					{translate('<strong>SMTP Mailer Settings:</strong><br />The settings below are only required if you want to send mails via <acronym title="Simple mail transfer protocol">SMTP</acronym>. If you do not know your SMTP host or you are not sure about the required settings, simply stay with the default mail routine: PHP MAIL.')}
				</p>
				<div class="clear"></div>
				<hr />
				<label class="fc_label_200" for="fc_catmailer_smtp_host">{translate('SMTP host')}:</label>
				<input type="text" name="catmailer_smtp_host" id="fc_catmailer_smtp_host" value="{$values.catmailer_smtp_host}"{if $CATMAILER_SMTP_AUTH} checked="checked"{/if}/>
				<div class="clear_sp"></div>
	
				<div class="fc_settings_max">
					<input type="checkbox" class="fc_checkbox_jq fc_toggle_element show___fc_smtp_aut" name="catmailer_smtp_auth" id="fc_catmailer_smtp_auth" value="true" {if $CATMAILER_SMTP_AUTH && $CATMAILER_ROUTINE=='smtp'} checked="checked"{/if}/>
					<label for="fc_catmailer_smtp_auth">{translate('SMTP authentification')}</label>
				</div>
				<div class="clear"></div>
				<p class="fc_important">({translate('only activate if your SMTP host requires authentification')})</p>

				<div id="fc_smtp_aut"{if $CATMAILER_ROUTINE == 'phpmail'} class="fc_inactive_element"{/if}>
				<hr />
					<div class="fc_settings_max">
						<label class="fc_label_120" for="fc_catmailer_smtp_username">{translate('SMTP username')}:</label>
						<input type="text" name="catmailer_smtp_username" id="fc_catmailer_smtp_username" value="{$values.catmailer_smtp_username}" />
						<div class="clear"></div>
						<label class="fc_label_120" for="fc_catmailer_smtp_password">{translate('SMTP password')}:</label>
						<input type="password" name="catmailer_smtp_password" id="fc_catmailer_smtp_username" value="{$values.catmailer_smtp_password}" />
					</div>
				</div>
			</div>
			{else}
			<input type="hidden" name="catmailer_routine" value="{$CATMAILER_ROUTINE}" />
			<input type="hidden" name="catmailer_smtp_host" value="{$values.catmailer_smtp_host}" />
			<input type="hidden" name="catmailer_smtp_auth" value="{$CATMAILER_SMTP_AUTH}" />
			<input type="hidden" name="catmailer_smtp_username" value="{$values.catmailer_smtp_username}" />
			<input type="hidden" name="catmailer_smtp_password" value="{$values.catmailer_smtp_password}" />
			{/if}

			<div class="clear"></div>
			<hr />
			<button class="icon-mail right" dir="ltr" type="submit" id="fc_checkmail" name="checkmail" onclick="send_testmail('{$CAT_ADMIN_URL}/settings/ajax_testmail.php');return false;"> {translate('Send test mail')}</button>
			<div class="clear_sp"></div>
			<p class="submit_settings fc_gradient1">
				<input type="submit" name="submit" value="{translate('Save')}" />
				<input type="reset" name="reset" value="{translate('Reset')}" />
			</p>
		</div><!-- MAIL -->

        <div id="fc_list_SEC" class="fc_gradient1 fc_list_forms ui-corner-top">
            <label class="fc_label_200" for="fc_default_template">{translate('Security settings')}:</label>

            <div class="fc_settings_max">

                <input type="checkbox" class="fc_checkbox_jq" name="auto_disable_users" id="fc_auto_disable_users" value="true" {if $AUTO_DISABLE_USERS} checked="checked"{/if}/>
    			<label for="fc_auto_disable_users">{translate('Disable user accounts when max login attempts is reached')}</label><br />

                <input type="checkbox" class="fc_checkbox_jq" name="enable_csrfmagic" id="fc_enable_csrfmagic" value="true" {if $ENABLE_CSRFMAGIC} checked="checked"{/if}/>
    			<label for="fc_enable_csrfmagic">{translate('Use csrf-magic to protect forms (frontend only)')}</label><br />

                <input type="checkbox" value="true" class="fc_checkbox_jq" name="csrfmagic_defer" id="fc_csrfmagic_defer" {if $CSRFMAGIC_DEFER} checked="checked"{/if}/>
                <label for="fc_csrfmagic_defer">{translate('Defer executing csrf_check() until manual call')}</label>
                
            </div>
            <div class="clear_sp"></div>

			<p class="submit_settings fc_gradient1">
				<input type="submit" name="submit" value="{translate('Save')}" />
				<input type="reset" name="reset" value="{translate('Reset')}" />
			</p>

        </div><!-- SEC -->

        <div id="fc_list_SYSINFO" class="fc_gradient1 fc_list_forms ui-corner-top">
            <label class="fc_label_200" for="fc_default_template">{translate('GUID')}:</label>
			<span id="guid">{$values.guid}</span>
            {if ! isset($values.guid) || $values.guid == ''}
            <button class="icon-shield" dir="ltr" type="submit" id="fc_createguid" name="create_guid" onclick="return false;">{translate('Create GUID')}</button>
            {/if}
   
			<hr />

			<span class="fc_label_200">{translate('Install date and time')}:</span>
			{$values.installation_time}
			<div class="clear_sp"></div>

            {if isset($values.pages_count) && is_array($values.pages_count)}
            <span class="fc_label_200">{translate('Page statistics')}:</span><br />
            {foreach $values.pages_count line}
            <span class="fc_label_120">{translate($line.visibility)}:</span>{$line.count}<br />
            {/foreach}
            {/if}
            <div class="clear_sp"></div>

        </div><!-- SYSINFO -->
        </div>
</form><!-- settings -->
{*<script src="{$CAT_ADMIN_URL}/settings/setting.js" type="text/javascript"></script>*}
</div>