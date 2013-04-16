<form action="{$CAT_URL}/account/login.php" method="post">
  <input type="hidden" name="username_fieldname" value="{$username_fieldname}" />
  <input type="hidden" name="password_fieldname" value="{$password_fieldname}" />
  <input type="hidden" name="redirect" value="{$redirect_url}" />
  <fieldset class="account_form">
    <legend class="account_legend">{translate('Login')}</legend>
    {if $message}<div class="account_message">{$message}</div>{/if}
    <label class="account_label" for="{$username_fieldname}">{translate('Username')}:</label>
      <input type="text" class="account_input" name="{$username_fieldname}" id="{$username_fieldname}" maxlength="30" /><br />
      <script type="text/javascript">
    	var ref= document.getElementById("{$username_fieldname}");
    	if (ref) ref.focus();
      </script>
    <label class="account_label" for="{$password_fieldname}">{translate('Password')}:</label>
      <input type="password" class="account_input" name="{$password_fieldname}" id="{$password_fieldname}" maxlength="30" /><br />
    <input type="submit" class="account_button" name="submit" value="{translate('Login')}"  />
	<input type="reset" class="account_button" name="reset" value="{translate('Reset')}"  />
  </fieldset>
</form><br />

<a href="{$CAT_URL}/account/forgot.php">{translate('Forgot your details?')}</a>