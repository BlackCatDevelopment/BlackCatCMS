			<label class="fc_label_120" for="fc_list_FRONTEND_signup">{translate('Signup')}:</label>
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
			</select><br /><br />
            <div class="fc_settings_max">
			    <input type="checkbox" class="fc_checkbox_jq" name="users_allow_mailaddress" id="fc_users_allow_mailaddress" value="true" {if $users_allow_mailaddress} checked="checked"{/if}/>
			    <label for="fc_users_allow_mailaddress">{translate('Allow mail address as login name')}</label>
            </div>

			{/if}
			<hr />
			<div class="fc_settings_max">
				<input type="checkbox" class="fc_checkbox_jq" name="frontend_login" id="fc_list_FRONTEND_login" value="true" {if $FRONTEND_LOGIN} checked="checked"{/if}/>
				<label for="fc_list_FRONTEND_login">{translate('Login')}</label>
			</div>
			<hr />
			<div class="fc_settings_max">
				<input type="checkbox" class="fc_checkbox_jq" name="home_folders" id="fc_home_folders" value="true" {if $HOME_FOLDERS} checked="checked"{/if}/>
				<label for="fc_home_folders">{translate('Personal folders')}</label>
			</div>
			<div class="clear_sp"></div>
			<p class="submit_settings fc_gradient1">
				<input type="submit" name="submit" value="{translate('Save')}" />
				<input type="reset" name="reset" value="{translate('Reset')}" />
			</p>