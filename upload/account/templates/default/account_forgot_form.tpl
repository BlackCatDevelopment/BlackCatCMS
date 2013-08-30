<h2>{translate('Forgot your details?')}</h2>
{if $message}<div class="account_message {$message_class}">{$message}</div>{/if}
{if $contact}<br />{$contact}<br />{/if}
{if $display_form}
<form name="forgot_pass" action="{$CAT_URL}/account/forgot.php" method="post">
  <fieldset class="account_form">
    <legend class="account_legend">{translate('Forgot your details?')}</legend>
    <label class="account_label" for="email">{translate('eMail')}:</label>
      <input type="text" class="account_button" maxlength="255" name="email" value="{$email}" style="width: 180px;" />
      <input type="submit" class="account_button" name="submit" value="{translate('Send details')}" />
  </fieldset>
</form>
{/if}