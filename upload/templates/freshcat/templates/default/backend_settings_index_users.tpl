			<label class="fc_label_200" for="fc_list_FRONTEND_signup">{translate('Signup')}:</label>
			{if !$groups.viewers}
			<div id="fc_list_FRONTEND_signup">
				{translate('No groups found')}
				<input type="hidden" name="frontend_signup" value="false" />
			</div>
			{else}
			<select name="frontend_signup" id="fc_list_FRONTEND_signup">
				<option value="false">--- {translate('Disabled')} ---</option>
				{foreach $groups.viewers group}
				<option value="{$group.VALUE}"{if $group.CHECKED} selected="selected"{/if}>{$group.NAME}</option>
				{/foreach}
			</select>
            <p>{translate('Allow visitors to sign-up from the frontend, to become members of your site. Anyone signing up will be <strong>automatically accepted</strong> and will become a member of the group you select here.')}</p>
            <hr />

            <div class="fc_settings_max">
			    <input type="checkbox" class="fc_checkbox_jq" name="users_allow_mailaddress" id="fc_users_allow_mailaddress" value="true" {if $values.users_allow_mailaddress} checked="checked"{/if}/>
			    <label for="fc_users_allow_mailaddress">{translate('Allow mail address as login name')}</label>
            </div>
			{/if}

			<div class="fc_settings_max">
				<input type="checkbox" class="fc_checkbox_jq" name="frontend_login" id="fc_list_FRONTEND_login" value="true" {if $values.frontend_login} checked="checked"{/if}/>
				<label for="fc_list_FRONTEND_login">{translate('Allow frontend login')}</label>
			</div>
            <div class="fc_settings_max">
                <input type="checkbox" class="fc_checkbox_jq" name="initial_page" id="fc_initial_page" value="true" {if $values.initial_page} checked="checked"{/if}/>
                <label for="fc_initial_page">{translate('Use initial page')}</label>
            </div>
			<div class="fc_settings_max">
				<input type="checkbox" class="fc_checkbox_jq" name="home_folders" id="fc_home_folders" value="true" {if $values.home_folders} checked="checked"{/if}/>
				<label for="fc_home_folders">{translate('Personal folders')}</label>
			</div>
			<div class="clear_sp"></div><hr />

            <label class="fc_label_200" for="fc_auth_min_login_length">{translate('Min. Login name length')}:</label>
			<input type="text" name="auth_min_login_length" id="fc_auth_min_login_length" value="{$values.auth_min_login_length}" />
            {translate('Should be at least')}: 5
			<div class="clear_sp"></div>
            <label class="fc_label_200" for="fc_auth_max_login_length">{translate('Max. Login name length')}:</label>
			<input type="text" name="auth_max_login_length" id="fc_auth_max_login_length" value="{$values.auth_max_login_length}" />
            {translate('Set to at least 255 if mail address is allowed!')}
			<div class="clear_sp"></div>

            <label class="fc_label_200" for="fc_auth_min_pass_length">{translate('Min. password length')}:</label>
			<input type="text" name="auth_min_pass_length" id="fc_auth_min_login_length" value="{$values.auth_min_pass_length}" />
            {translate('Should be at least')} 5; {translate('for better security, choose 16 or more')}
			<div class="clear_sp"></div>
            <label class="fc_label_200" for="fc_auth_max_pass_length">{translate('Max. password length')}:</label>
			<input type="text" name="auth_max_pass_length" id="fc_auth_max_pass_length" value="{$values.auth_max_pass_length}" />
			<div class="clear_sp"></div>