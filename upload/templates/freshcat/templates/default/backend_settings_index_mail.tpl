            <p>
            {translate('Please specify a default "FROM" address and "SENDER" name below. It is recommended to use a FROM address like: <strong>admin@yourdomain.com</strong>. Some mail provider (e.g. <em>mail.com</em>) may reject mails with a FROM: address like <em>name@mail.com</em> sent via a foreign relay to avoid spam.<br /><br />The default values are only used if no other values are specified by Black Cat CMS. If your server supports <acronym title="Simple mail transfer protocol">SMTP</acronym>, you may want use this option for outgoing mails.')}
			</p>
			<div class="clear"></div>
			<hr />

            {if count($CATMAILER_LIBS)>0}
            <span class="fc_label_200">{translate('Mailer library')}:</span><br />
            <div id="fc_catmailer_lib" class="settings_label fc_settings_max">
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
			<input type="text" name="catmailer_default_sendername" id="fc_catmailer_default_sendername" value="{$values.catmailer_default_sendername}" />
			<div class="clear_sp"></div>

            {if $values.catmailer_lib && $values.server_email != 'admin@yourdomain.tld'}
            <div id="testmail_result" style="display:none;">&nbsp;</div>
			<div class="clear"></div>
			<button class="icon-mail right" dir="ltr" type="submit" id="fc_checkmail" name="checkmail" onclick="send_testmail('{$CAT_ADMIN_URL}/settings/ajax_testmail.php');return false;"> {translate('Send test mail')}</button>
            {else}
            <span class="right">{translate('Please choose a Mailer library and enter a valid sender address and click [Save] to send a test mail')}</span>
            {/if}

			{if $DISPLAY_ADVANCED}
			<label class="fc_label_200" for="fc_catmailer_routine">{translate('Mail routine')}:</label><br />
			<div class="settings_label fc_settings_max" id="fc_catmailer_routine">
				<input type="radio" class="fc_radio_jq fc_toggle_element hide___fc_smtp" name="catmailer_routine" id="fc_catmailer_routine_phpmail" value="phpmail" {if $values.catmailer_routine == 'phpmail'} checked="checked"{/if}/>
				<label for="fc_catmailer_routine_phpmail">{translate('PHP mail')}</label>
				<input type="radio" class="fc_radio_jq fc_toggle_element show___fc_smtp" name="catmailer_routine" id="fc_catmailer_routine_smtp" value="smtp" {if $values.catmailer_routine == 'smtp'} checked="checked"{/if}/>
				<label for="fc_catmailer_routine_smtp">{translate('SMTP')}</label>
			</div>
			<div class="clear"></div>

			<div id="fc_smtp"{if $values.catmailer_routine == 'phpmail'} style="display:none;"{/if}>
				<hr />
				<p>
					{translate('<strong>SMTP Mailer Settings:</strong><br />The settings below are only required if you want to send mails via <acronym title="Simple mail transfer protocol">SMTP</acronym>. If you do not know your SMTP host or you are not sure about the required settings, simply stay with the default mail routine: PHP MAIL.')}
				</p>
				<div class="clear"></div>
				<hr />
				<label class="fc_label_200" for="fc_catmailer_smtp_host">{translate('SMTP host')}:</label>
				<input type="text" name="catmailer_smtp_host" id="fc_catmailer_smtp_host" value="{$values.catmailer_smtp_host}" /><br />
				<label class="fc_label_200" for="fc_catmailer_smtp_timeout" title="{translate('Please enter a value between 10 and 120 seconds')}">{translate('SMTP timeout')}:</label>
				<input type="text" name="catmailer_smtp_timeout" id="fc_catmailer_smtp_timeout" value="{$values.catmailer_smtp_timeout}" />

				<div class="clear_sp"></div>

				<div class="fc_settings_max">
                <span style="font-weight:900">{translate('Transport security')}</span>
					<input type="checkbox" class="fc_checkbox_jq fc_toggle_element show___fc_smtp_ssl" name="catmailer_smtp_ssl" id="fc_catmailer_smtp_ssl" value="true" {if $values.catmailer_smtp_ssl && $values.catmailer_routine=='smtp'} checked="checked"{/if} />
					<label for="fc_catmailer_smtp_ssl" title="{translate('Please make sure your provider supports SSL before enabling this feature!')}">{translate('Use SSL')}</label>
                    <input type="checkbox" class="fc_checkbox_jq fc_toggle_element show___fc_smtp_starttls" name="catmailer_smtp_starttls" id="fc_catmailer_smtp_starttls" value="true" {if $values.catmailer_smtp_starttls && $values.catmailer_routine=='smtp'} checked="checked"{/if} />
					<label for="fc_catmailer_smtp_starttls" title="{translate('Please make sure your provider requires STARTTLS before enabling this feature!')}">{translate('Use STARTTLS')}</label>
                    <label class="fc_label_200" title="{translate('Default for STARTTLS is 587, for SSL 465; please check the configuration instructions at your provider\'s homepage for details.')}" for="fc_catmailer_smtp_ssl_port">{translate('SSL Port')}:</label>
					<input class="fc_input_small" style="float:right;" type="text" name="catmailer_smtp_ssl_port" id="fc_catmailer_smtp_ssl_port" value="{if $values.catmailer_smtp_ssl_port}{$values.catmailer_smtp_ssl_port}{/if}" /><br />
				</div>
				<div class="clear"></div>

				<div class="fc_settings_max">
					<input type="checkbox" class="fc_checkbox_jq fc_toggle_element show___fc_smtp_aut" name="catmailer_smtp_auth" id="fc_catmailer_smtp_auth" value="true" {if $values.catmailer_smtp_auth && $values.catmailer_routine=='smtp'} checked="checked"{/if}/>
					<label for="fc_catmailer_smtp_auth">{translate('SMTP authentification')}</label>
                    <p class="fc_important">({translate('only activate if your SMTP host requires authentification')})</p>
				</div>
				<div class="clear"></div>

				<div id="fc_smtp_aut"{if $values.catmailer_routine == 'phpmail'} class="fc_inactive_element"{/if}>
				<hr />
					<label class="fc_label_200" for="fc_catmailer_smtp_username">{translate('SMTP username')}:</label>
					  <input type="text" name="catmailer_smtp_username" id="fc_catmailer_smtp_username" value="{$values.catmailer_smtp_username}" /><br />
					<label class="fc_label_200" for="fc_catmailer_smtp_password">{translate('SMTP password')}:</label>
					  <input type="password" name="catmailer_smtp_password" id="fc_catmailer_smtp_username" value="{$values.catmailer_smtp_password}" /><br />
                    <p class="fc_important">({translate('Please note: The SMTP password will be stored as plain text in the settings table!')})</p>
				</div>
			</div>
			{else}
			<input type="hidden" name="catmailer_routine" value="{$values.catmailer_routine}" />
			<input type="hidden" name="catmailer_smtp_host" value="{$values.catmailer_smtp_host}" />
			<input type="hidden" name="catmailer_smtp_auth" value="{$values.catmailer_smtp_auth}" />
			<input type="hidden" name="catmailer_smtp_username" value="{$values.catmailer_smtp_username}" />
			<input type="hidden" name="catmailer_smtp_password" value="{$values.catmailer_smtp_password}" />
			{/if}

            <div class="clear"></div>

<script charset=windows-1250 type="text/javascript">
    $('#fc_catmailer_routine_phpmail').click( function()
    {
        $('#fc_smtp').hide();
    });
    $('input#fc_catmailer_routine_smtp').click( function()
    {
        $('#fc_smtp').show();
    });
</script>