<div id="fc_content_header">
	{translate('My settings')}
</div>
<div id="fc_main_content">
	{if $show_cmd_profile_edit_block}
	<form name="CMD_PROFILE_EDIT" action="{$PROFILE_ACTION_URL}" method="post" class="fc_gradient1">
		<input type="hidden" name="user_id" value="{$USER_ID}" />
		<input type="submit" name="cmd_profile_edit" value="{translate('Change the profile')}" />
	</form>
	</div>
	{/if}
	<div class="fc_modified_header fc_gradient1">
		{translate('Username')}: <strong>{$USERNAME}</strong>
	</div>
	<form name="preferences_form" id="fc_preferences_form" action="{$CAT_ADMIN_URL}/preferences/ajax_save.php" method="post" class="ajaxForm">
        <input type="hidden" name="_cat_ajax" value="1" />
		<p class="submit_settings fc_gradient1 fc_border">
			<input type="submit" class="fc_preferences_submit" name="submit" value="{translate('Save')}" />
			<input type="reset" class="fc_modifyUser_reset" name="reset" value="{translate('Reset')}" />
		</p>
		<div class="fc_list_forms fc_gradient1">
			<label for="fc_pref_display_name" class="fc_label_300">{translate('Display name')}:</label>
			<input type="text" id="fc_pref_display_name" name="display_name" value="{$DISPLAY_NAME}" />
			<div class="clear_sp"></div>
		
			<label for="fc_language" class="fc_label_300">{translate('Language')}:</label>
			<select name="language" id="fc_language">
				{foreach $languages as language}
				<option value="{$language.VALUE}"{if $language.SELECTED} selected="selected"{/if} style="background: url({$CAT_URL}/languages/{$language.VALUE}.png) no-repeat center left; padding-left: 20px;">{$language.NAME} ({$language.VALUE})</option>
				{/foreach}
			</select>
			<div class="clear_sp"></div>
		
			<label for="fc_timezone_string" class="fc_label_300">{translate('Timezone')}:</label>
			<select name="timezone_string" id="fc_timezone_string">
				{foreach $timezones as timezone}
				<option{if $timezone.SELECTED} selected="selected"{/if}>{$timezone.NAME}</option>
				{/foreach}
			</select>
			<div class="clear_sp"></div>
		
			<label for="fc_date_format" class="fc_label_300">{translate('Date format')}:</label>
			<select name="date_format" id="fc_date_format">
				{foreach $dateformats as dateformat}
				<option value="{$dateformat.VALUE}"{if $dateformat.SELECTED} selected="selected"{/if}>{$dateformat.NAME}</option>
				{/foreach}
			</select>
			<div class="clear_sp"></div>
		
			<label for="fc_time_format" class="fc_label_300">{translate('Time format')}:</label>
			<select name="time_format" id="fc_time_format">
				{foreach $timeformats as timeformat}
					<option value="{$timeformat.VALUE}"{if $timeformat.SELECTED} selected="selected"{/if}>{$timeformat.NAME}</option>
				{/foreach}
			</select>
			<div class="clear_sp"></div>
			<div class="hidden">
				<label for="fc_init_page_select">{$INIT_PAGE_LABEL.label_default}:</label>
				{$INIT_PAGE_SELECT}
				<div class="clear_sp"></div>
			</div>
		
			<label for="fc_email" class="fc_label_300">{translate('Email')}:</label>
			<input type="text" id="fc_email" name="email" value="{$EMAIL}" />
			<div class="clear_sp"></div>

            <div id="fc_modifyUser_currentpw" class="fc_modifyUser" style="display:none;">
                <div class="fc_modifyUser fc_password_notification fc_br_all icon-notification fc_gradient_red fc_input_description">
    				{translate('Please enter your CURRENT password to confirm your changes!')}
    			</div>
                <div class="clear_sp"></div>
    			<label for="fc_current_password" class="fc_label_300">{translate('Confirm with current password')}:</label>
    			<input type="password" id="fc_current_password" name="current_password" value="" />
                <div class="clear_sp"></div>
            </div>

            <div class="fc_modifyUser">
                <button id="fc_change_pw" class="fc_gradient_blue">
                 {translate('Change password')}
                </button>
            </div><div class="clear_sp"></div>
            <div id="fc_modifyUser_setnewpw" class="fc_modifyUser" style="display:none;">
            <div class="fc_modifyUser fc_password_notification fc_br_all icon-notification fc_gradient_red fc_input_description">
				{translate('Please note: You should only enter values in those fields if you wish to change this users password')}
			</div><div class="clear_sp"></div>

			<label for="fc_new_password_1" class="fc_label_300">{translate('New password')}:</label>
			<input type="password" id="fc_new_password_1" name="new_password_1" value="" />
			<div class="clear_sp"></div>
	
			<label for="fc_new_password_2" class="fc_label_300">{translate('Re-Type new password')}:</label>
			<input type="password" id="fc_new_password_2" name="new_password_2" value="" />
			<div class="clear_sp"></div>
            </div>
		
		</div>
		<p class="submit_settings fc_gradient1 fc_border fc_text_right">
			<input type="submit" class="fc_preferences_submit" name="submit" value="{translate('Save')}" />
			<input type="reset" class="fc_modifyUser_reset" name="reset" value="{translate('Reset')}" />
		</p>
	</form>
</div>