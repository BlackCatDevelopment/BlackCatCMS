<form action="{cat_url}/account/login.php" method="post">
  <input type="hidden" name="username_fieldname" value="{$username_fieldname}" />
  <input type="hidden" name="password_fieldname" value="{$password_fieldname}" />
  <input type="hidden" name="redirect" value="{$redirect_url}" />
  <fieldset class="account_form">
    <legend class="account_legend">{translate('Login')}</legend>
    {if $message}<div class="account_message">{$message}</div>{/if}
	<p {if $otp}style="display:none;"{/if}>
    	<label class="account_label" for="{$username_fieldname}">{translate('Username')}:</label>
		<input type="text" class="account_input" name="{$username_fieldname}" id="{$username_fieldname}" maxlength="30" value="{$user}" /><br />
    	{if !$otp}<script type="text/javascript">
    		var ref= document.getElementById("{$username_fieldname}");
    		if (ref) ref.focus();
    	</script>{/if}
	</p>
	<p>
		<label class="account_label" for="{$password_fieldname}">{if !$otp}{translate('Password')}{else}{translate('Current password')}{/if}:</label>
		<input type="password" class="account_input" name="{$password_fieldname}" id="{$password_fieldname}" maxlength="30" />
	</p>
	<p {if !$otp}style="display:none;"{/if}>
    	<label class="account_label" for="{$password_fieldname}_1">{translate('New password')}:</label>
		<input type="password" class="account_input" name="{$password_fieldname}_1" id="{$password_fieldname}_1" maxlength="30" /><br />
    	{if $otp}<script type="text/javascript">
    		var ref= document.getElementById("{$password_fieldname}_1");
    		if (ref) ref.focus();
    	</script>{/if}
		<label class="account_label" for="{$password_fieldname}_2">{translate('Retype new password')}:</label>
		<input type="password" class="account_input" name="{$password_fieldname}_2" id="{$password_fieldname}_2" maxlength="30" /><br />
    </p>
    <p class="warning">
      <input type="checkbox" name="fc_cookie_allow" id="fc_cookie_allow" value="true">
      <label for="fc_cookie_allow">{translate('A technical cookie is required for login.')}</label>
      {translate('allow')}
  </p>
    <input type="submit" class="account_button" name="submit" value="{translate('Login')}"  />
	<input type="reset" class="account_button" name="reset" value="{translate('Reset')}"  />
  </fieldset>
</form><br />

<a href="{$CAT_URL}/account/forgot.php">{translate('Forgot your details?')}</a>