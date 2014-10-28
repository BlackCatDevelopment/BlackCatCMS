			<label class="fc_label_200" for="fc_list_FRONTEND_signup" title="{translate('Allows visitors to sign-up from the frontend to become members of your site and get access to special regions. Any sign-up will be accepted automatically and the new user will become a member of the group you select here.')}">{translate('Signup')}:</label>
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
            <hr />
			{/if}

            <div class="fc_settings_max">
			    <input type="checkbox" class="fc_checkbox_jq" name="users_allow_mailaddress" id="fc_users_allow_mailaddress" value="true" {if $values.users_allow_mailaddress} checked="checked"{/if}/>
			    <label for="fc_users_allow_mailaddress" title="{translate('Allows to use email addresses as login names. Influences the list of allowed chars in the user login.')}">{translate('Allow mail address as login name')}</label>
            </div>
			<div class="fc_settings_max">
				<input type="checkbox" class="fc_checkbox_jq" name="frontend_login" id="fc_list_FRONTEND_login" value="true" {if $values.frontend_login} checked="checked"{/if}/>
				<label for="fc_list_FRONTEND_login" title="{translate('If the frontend template supports this, a login box is rendered on the frontpage. You may also use the Login box Droplet for this case.')}">{translate('Allow frontend login')}</label>
			</div>
            <div class="fc_settings_max">
                <input type="checkbox" class="fc_checkbox_jq" name="initial_page" id="fc_initial_page" value="true" {if $values.initial_page} checked="checked"{/if}/>
                <label for="fc_initial_page" title="{translate('A user can have an individual start page in the backend. Enable this option to use this feature. The start page is set in the user settings.')}">{translate('Use initial page')}</label>
            </div>
			<div class="fc_settings_max">
				<input type="checkbox" class="fc_checkbox_jq" name="home_folders" id="fc_home_folders" value="true" {if $values.home_folders} checked="checked"{/if}/>
				<label for="fc_home_folders" title="{translate('Allows to assign user based home folders located under media. Please remember to create the folders in the media section. To assign a home folder to a user, proceed to the user settings.')}">{translate('Personal folders')}</label>
			</div>
			<div class="clear_sp"></div><hr />

            <label class="fc_label_200" for="fc_auth_min_login_length" title="{translate('Allows to set a minimal login name length. Good values start with 5 chars. Please note that this should not be changed if a large number of users already exists.')}">{translate('Min. Login name length')}:</label>
			<input type="text" name="auth_min_login_length" id="fc_auth_min_login_length" value="{$values.auth_min_login_length}" />
            {translate('Should be at least')}: 5
			<div class="clear_sp"></div>
            <label class="fc_label_200" for="fc_auth_max_login_length" title="{translate('Allows to set a maximal login name length. Set a higher value if you allow email addresses as login names.')}">{translate('Max. Login name length')}:</label>
			<input type="text" name="auth_max_login_length" id="fc_auth_max_login_length" value="{$values.auth_max_login_length}" />
            {translate('Set to at least 255 if mail address is allowed!')}
			<div class="clear_sp"></div>

            <label class="fc_label_200" for="fc_auth_min_pass_length" title="{translate('Allows to set a minimal password length. Please note that longer passwords are more secure.')}">{translate('Min. password length')}:</label>
			<input type="text" name="auth_min_pass_length" id="fc_auth_min_login_length" value="{$values.auth_min_pass_length}" />
            {translate('Should be at least')} 5; {translate('for better security, choose 16 or more')}
			<div class="clear_sp"></div>
            <label class="fc_label_200" for="fc_auth_max_pass_length" title="{translate('Allows to set a maximal password length. You should not restrict the maximal length too much.')}">{translate('Max. password length')}:</label>
			<input type="text" name="auth_max_pass_length" id="fc_auth_max_pass_length" value="{$values.auth_max_pass_length}" />
			<div class="clear_sp"></div>