<div class="result_message">{$RESULT_MESSAGE}</div>
<noscript>
    <div class="result_message">{translate('Please enable JavaScript to use this form.')}</div>
</noscript>
<form name="user" action="{$PREFERENCES_URL}" method="post" style="margin-bottom: 5px;" onsubmit="return pf_check_form();">
	<input type="hidden" name="user_id" value="{$USER_ID}" />
	<input type="hidden" name="save" value="account_settings" />
	<fieldset class="account_form">
		<legend>{translate('My preferences')}</legend>
		<div class="account_label">{translate('Display name')}:</div>
		<div class="account_value"><input type="text" name="display_name" maxlength="255" value="{$DISPLAY_NAME}" /></div>
		
		<div class="account_label">{translate('Language')}:</div>
		<div class="account_value">
			<select name="language">
            {foreach $languages lang}
			    <option value="{$lang.VALUE}" {if $lang.SELECTED==1}selected="selected"{/if}>{$lang.NAME} ({$lang.VALUE})</option>
            {/foreach}
			</select>
		</div>

		<div class="account_label">{translate('Timezone')}:</div>
		<div class="account_value">
			<select name="timezone_string">
            {foreach $timezones tz}
			    <option value="{$tz}" {if $tz == $current_tz}selected="selected"{/if}>{$tz}</option>
            {/foreach}
			</select>
		</div>

		<div class="account_label">{translate('Date format')}:</div>
		<div class="account_value">
			<select name="date_format">
			<option value="">{translate('Please select')}...</option>
            {foreach $date_formats format title}
			    <option value="{$format}" {if $format == $current_df}selected="selected"{/if}>{$title}</option>
			{/foreach}
			</select>
		</div>

		<div class="account_label">{translate('Time format')}:</div>
		<div class="account_value">
			<select name="time_format">
			<option value="">{translate('Please select')}...</option>
			{foreach $time_formats format title}
			    <option value="{$format}" {if $format == $current_tf}selected="selected"{/if}>{$title}</option>
			{/foreach}
			</select>
		</div>

	</fieldset>

	<fieldset class="account_form">
        <legend>{translate('eMail')}</legend>
	    <div class="account_label">{translate('eMail')}:</div>
		<div class="account_value">
			<input type="text" name="email" maxlength="255" value="{$GET_EMAIL}" />
		</div>
	</fieldset>

	<fieldset class="account_form">
		<legend>{translate('Password')}</legend>
        <div class="icon-notification account_message">
            {translate('Please note: You should only enter values in those fields if you wish to change this users password')}
        </div><br /><br />
		<div class="account_label">{translate('New password')}:</div>
		<div class="account_value"><input type="password" name="new_password" /></div>
		<div class="account_label">{translate('Re-Type new password')}:</div>
		<div class="account_value"><input type="password" name="new_password2" /></div>
	</fieldset>

	<fieldset class="account_form">
		<legend>{translate('Confirm')}</legend>
        <div class="icon-notification account_message">
			{translate('Please enter your CURRENT password to confirm your changes!')}
		</div><br /><br />
		<div class="account_label">{translate('Confirm with current password')}:</div>
		<div class="account_value"><input type="password" name="current_password" id="current_password" onkeyup="check_input(this);"/></div>
	</fieldset>

	<input type="submit" name="submit" value="{translate('Save')}" disabled='disabled' id="user_submit" />
	<input type="reset" name="reset" value="{translate('Reset')}" />
</form>
<script type="text/javascript">

function pf_check_form() {
	var ref = document.getElementById("current_password");
	if (ref) {
		if (ref.value == "") {
			alert ("Please confirm the changes by your current password!");
			return false;
		} else {
			return true;
		}
	}
	return false;
}

function check_input(aRef) {
	var ref = document.getElementById("user_submit");
	if (aRef.value == "") {
		ref.disabled = true;
	} else {
		ref.disabled = (aRef.value.length >= {$AUTH_MIN_LOGIN_LENGTH}) ? false : true;
	}
}
</script>