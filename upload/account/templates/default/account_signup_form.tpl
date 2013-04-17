<h1>{translate('Sign-up')}</h1>
{if $message}<div class="account_message">{$message}</div>{/if}
{if $form}
<form name="user" action="{$SIGNUP_URL}" method="post">
    <fieldset class="account_form">
        <legend class="account_legend">{translate('Sign-up')}</legend>
{if $ENABLED_ASP}
<div style="display:none;">
	<input type="hidden" name="submitted_when" value="{$submitted_when}" />
	<p class="nixhier">
	email-address:
	<label for="email-address">Leave this field email-address blank:</label>
	<input id="email-address" name="email-address" size="60" value="" /><br />
	username (id):
	<label for="name">Leave this field name blank:</label>
	<input id="name" name="name" size="60" value="" /><br />
	Full Name:
	<label for="full_name">Leave this field full_name blank:</label>
	<input id="full_name" name="full_name" size="60" value="" /><br />
	</p>
    </div>
{/if}
        <label class="account_label" for="username">{translate('Username')}</label>
          <input type="text" maxlength="30" name="username" id="username" tabindex="1" value="{$username}" class="account_input" /><br />
        <label class="account_label" for="display_name">{translate('Display name')} ({translate('Full name')})</label>
          <input type="text" maxlength="255" name="display_name" id="display_name" tabindex="2" value="{$display_name}" class="account_input" /><br />
        <label class="account_label" for="email">{translate('eMail')}</label>
          <input type="text" maxlength="30" name="email" id="email" tabindex="3" value="{$email}" class="account_input" /><br />
{if $captcha}
        <label class="account_label" for="captcha">{translate('Captcha verification')}</label>
          {$captcha}<br /><br />
{/if}
		<input class="account_button" type="submit" name="submit" value="{translate('Sign-up')}" />
		<input class="account_button" type="reset" name="reset" value="{translate('Reset')}" />
    </fieldset>
</form><br />
{/if}