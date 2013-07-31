<div class="info">
{translate('Site settings')}
</div>

<table>
  <tbody>
    <tr{if $errors.installer_website_title || $errors.global} class="fail"{/if}>
      <td><label for="installer_website_title">{translate('Website title')}</label></td>
      <td>
        <input type="text" tabindex="1" name="installer_website_title" id="installer_website_title" style="width: 97%;" value="{$installer_website_title}" />
        {if $errors.installer_website_title}<br /><span>{$errors.installer_website_title}</span>{/if}
      </td>
	</tr>
	<tr{if $errors.installer_admin_username || $errors.global} class="fail"{/if}>
      <td><label for="installer_admin_username">{translate('Username')}</label></td>
      <td>
        <input type="text" tabindex="3" name="installer_admin_username" id="installer_admin_username" style="width: 97%;" value="{$installer_admin_username}" />
        {if $errors.installer_admin_username}<br /><span>{$errors.installer_admin_username}</span>{/if}
        <br /><span style="font-size: 10px; color: #666666;">{translate("at least 5 chars")}</span>
	  </td>
	</tr>
	<tr{if $errors.installer_admin_email || $errors.global} class="fail"{/if}>
      <td><label for="installer_admin_email">{translate('E-Mail')}</label></td>
      <td>
        <input type="text" tabindex="4" name="installer_admin_email" id="installer_admin_email" style="width: 97%;" value="{$installer_admin_email}" />
        {if $errors.installer_admin_email}<br /><span>{$errors.installer_admin_email}</span>{/if}
	  </td>
	</tr>
    <tr{if $errors.installer_admin_password || $errors.global} class="fail"{/if}>
      <td><label for="installer_no_validate_admin_password">{translate("Don't check admin password")}</label></td>
      <td>
        <input type="checkbox" tabindex="12" name="installer_no_validate_admin_password" id="installer_no_validate_admin_password" value="true" {if $installer_no_validate_admin_password}checked="checked"{/if} />
        <span style="font-size: 10px; color: #666666;">{translate("If you wish to set a password that doesn't meet common security constraints, please check this checkbox. Please note that this is a security risk in public environments! Use empty and/or short passwords in (local) testing environments only.")}</span>
      </td>
    </tr>
	<tr{if $errors.installer_admin_password || $errors.global} class="fail"{/if}>
      <td><label for="installer_admin_password">{translate('Password')}</label></td>
      <td>
    	<input type="password" tabindex="5" name="installer_admin_password" id="installer_admin_password" style="width: 98%;" value="{$installer_admin_password}" />
    	{if $errors.installer_admin_password}<br /><span>{$errors.installer_admin_password}</span>{/if}
      </td>
    </tr>
	<tr{if $errors.installer_admin_repassword || $errors.global} class="fail"{/if}>
      <td><label for="installer_admin_repassword">{translate('Retype Password')}</label></td>
      <td>
    	<input type="password" tabindex="6" name="installer_admin_repassword" id="installer_admin_repassword" style="width: 98%;" value="{$installer_admin_repassword}" />
    	{if $errors.installer_admin_repassword}<br /><span>{$errors.installer_admin_repassword}</span>{/if}
      </td>
    </tr>

  </tbody>
</table>

{if $errors.global}
<div id="result" class="fail">{$errors.global}</div>
{/if}