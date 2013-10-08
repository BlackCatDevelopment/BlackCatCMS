<div class="info">
{translate('Please enter your MySQL database server details below')}
</div>

{if $errors.global}
<div class="fail">{$errors.global}</div>
{/if}
<table>
  <tbody>
    <tr{if $errors.installer_database_host || $errors.global} class="fail"{/if}>
      <td><label for="installer_database_host">{translate('Host Name')}</label></td>
      <td>
    	<input type="text" tabindex="1" name="installer_database_host" id="installer_database_host" style="width: 98%;" value="{$installer_database_host}" />
    	{if $errors.installer_database_host}<br /><span>{$errors.installer_database_host}</span>{/if}
      </td>
    </tr>
    <tr{if $errors.installer_database_port || $errors.global} class="fail"{/if}>
      <td><label for="installer_database_port">{translate('Port')}</label></td>
      <td>
    	<input type="text" tabindex="2" name="installer_database_port" id="installer_database_port" style="width: 98%;" value="{$installer_database_port}" />
    	{if $errors.installer_database_port}<br /><span>{$errors.installer_database_port}</span>{/if}
      </td>
    </tr>
    <tr{if $errors.installer_database_name || $errors.global} class="fail"{/if}>
      <td><label for="installer_database_name">{translate('Database Name')}</label><br /><span style="font-size:smaller;">[a-zA-Z0-9_-]</span></td>
      <td>
    	<input type="text" tabindex="3" name="installer_database_name" id="installer_database_name" style="width: 98%;" value="{$installer_database_name}" />
    	{if $errors.installer_database_name}<br /><span>{$errors.installer_database_name}</span>{/if}
      </td>
    </tr>
    <tr{if $errors.installer_database_username || $errors.global} class="fail"{/if}>
      <td><label for="installer_database_username">{translate('Database User')}</label></td>
      <td>
    	<input type="text" tabindex="4" name="installer_database_username" id="installer_database_username" style="width: 98%;" value="{$installer_database_username}" />
    	{if $errors.installer_database_username}<br /><span>{$errors.installer_database_username}</span>{/if}
      </td>
    </tr>
	<tr{if $errors.installer_database_password || $errors.global || $errors.installer_database_password_empty} class="fail"{/if}>
      <td><label for="installer_no_validate_db_password">{translate("Don't check database password")}</label></td>
      <td>
        <input type="checkbox" tabindex="12" name="installer_no_validate_db_password" id="installer_no_validate_db_password" value="true" {if $installer_no_validate_db_password}checked="checked"{/if} />
		<label for="install_tables">{translate('Yes')}</label> 
        <span style="font-size: 10px; color: #666666;">({translate("If you don't have a database password, or a password that doesn't meet common security constraints, please check this checkbox. Please note that this is a security risk in public environments! Use empty and/or short passwords in (local) testing environments only.")})</span>
      </td>
    </tr>
	<tr{if $errors.installer_database_password || $errors.installer_database_password_empty || $errors.global} class="fail"{/if}>
      <td><label for="installer_database_password">{translate('Database Password')}</label></td>
      <td>
    	<input type="password" tabindex="5" name="installer_database_password" id="installer_database_password" style="width: 98%;" value="{$installer_database_password}" /><br />
    	{if $errors.installer_database_password}<br /><span>{$errors.installer_database_password}</span>{/if}
    	{if $errors.installer_database_password_empty}<br /><span>{translate("You have set an empty password. Please check the checkbox above to confirm that this is really what you want.")}</span>{/if}
      </td>
    </tr>
	<tr{if $errors.installer_table_prefix} class="fail"{/if}>
	  <td><label for="installer_table_prefix">{translate('Table Prefix')}</label><br /><span style="font-size:smaller;">[a-zA-Z0-9_]</span></td>
	  <td>
       <input type="text" tabindex="6" name="installer_table_prefix" style="width:98%;" value="{$installer_table_prefix}" />
       {if $errors.installer_table_prefix}<br /><span>{$errors.installer_table_prefix}</span>{/if}
	  </td>
    </tr>
	<tr>
      <td>{translate('Install Tables')}</td>
      <td colspan="6">
		<input type="checkbox" tabindex="12" name="installer_install_tables" id="installer_install_tables" value="true" {if $installer_install_tables}checked="checked"{/if} />
		<label for="install_tables">{translate('Yes')}</label>
        <span style="font-size: 10px; color: #666666;">({translate('Please note: May remove existing tables and data')})</span>
      </td>
	</tr>
  </tbody>
</table>
