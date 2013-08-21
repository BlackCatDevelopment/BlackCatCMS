		<div id="fc_list_{if $addon.directory}{$addon.directory}{else}{$addon.INSTALL.directory}{/if}" class="fc_list_forms fc_form_content">

{if $addon.is_removable && $addon.is_installed && $permissions.MODULES_UNINSTALL}
			<form name="uninstall" action="uninstall.php" method="post" class="submit_settings fc_gradient1">
				<input type="hidden" name="file" value="{$addon.directory}" />
				<input type="hidden" name="type" value="{$addon.type}" />
                <input type="hidden" name="{$csrfname}" value="{$csrftoken}" />
				<strong>{translate('Module details')}: {$addon.name}</strong>
				<input type="submit" name="uninstall_module" value="{translate('Uninstall Addon')}" class="fc_gradient_red" />
			</form>
{else}
            <div class="submit_settings fc_gradient1">
                <strong>{translate('Module details')}: {$addon.name}</strong>
                {if ! $addon.is_removable}
                <span>{translate('Marked as mandatory')}</span>
                {/if}
            </div>
{/if}
			<div class="clear_sp"></div>

{if $addon.description || $addon.type == 'languages'}
			{if $addon.description}
    			<div>
    				{if $addon.icon}<img class="right" src="{$addon.icon}" alt="{$addon.name}" />{/if}
    				{$addon.description}
    				<div class="clear"></div>
    			</div>
    			<div class="clear"></div>
    			<hr />
			{/if}
			<p>
			<span class="fc_label_200">{translate('Version')}:</span>{$addon.version}<br />
			<span class="fc_label_200">{translate('Author')}:</span>{$addon.author}<br />
            {if $addon.link}<span class="fc_label_200">{translate('Link')}:</span><a href="{$addon.link}" target="_blank">{$addon.link}</a><br />{/if}
			{if $addon.function}<span class="fc_label_200">{translate('Function')}:</span>{$addon.function}<br />{/if}
			<span class="fc_label_200">{translate('Designed for')}:</span>Black Cat CMS {$addon.platform}<br />
			<span class="fc_label_200">{translate('License')}:</span>{$addon.license}<br />
            {if $addon.installed}<span class="fc_label_200">{translate('Installed')}:</span>{$addon.installed}<br />{/if}
            {if $addon.upgraded}<span class="fc_label_200">{translate('Upgraded')}:</span>{$addon.upgraded}<br />{/if}
			</p>
			{if $permissions.MODULES_UNINSTALL && !$addon.UNINSTALLED}
    			<div class="clear"></div>
    			<hr />
    			<div class="clear_sp"></div>
			{/if}
{/if}

{if $permissions.MODULES_INSTALL && ! $addon.is_installed}
            <h2>{translate('Module seems to be not installed yet.')}</h2>
{/if}

{if $permissions.MODULES_INSTALL}
              {if $addon.type == 'module'}
                  <p class="fc_gradient_red">{translate('DANGER ZONE! This may delete your current data!')}</p>
    			  <p>{translate('When modules are uploaded via FTP (not recommended), the module installation functions install, upgrade or uninstall will not be executed automatically. Those modules may not work correct or do not uninstall properly.')}<br />
                  {translate('You can execute the module functions manually for modules uploaded via FTP below.')}
                  </p>
              {/if}

              {if $addon.type == 'template' || $addon.type == 'language' || $addon.INSTALL}
                  <form name="install" action="manual_install.php" method="post" style="float:left;">
    				<input type="hidden" name="action" value="install" />
                    <input type="hidden" name="file" value="{if $addon.directory}{$addon.directory}{else}{$addon.INSTALL.directory}{/if}" />
                    <input type="hidden" name="type" value="{$addon.type}" />
                    <input type="hidden" name="{$csrfname}" value="{$csrftoken}" />
    				<input type="submit" name="install_manual_module" class="fc_gradient_red" value="{translate('Install manually')}" />
    			  </form>
              {/if}

              {if $addon.type == 'module' && ! $addon.INSTALL}
                  <h3>{translate('No install.php found! The module cannot be installed!')}</h3>
              {/if}
{/if}

{if $permissions.MODULES_INSTALL && $addon.UPGRADE && $addon.is_installed}
			<form name="upgrade" action="manual_install.php" method="post">
				<input type="hidden" name="action" value="upgrade" />
                <input type="hidden" name="type" value="{$addon.type}" />
                <input type="hidden" name="{$csrfname}" value="{$csrftoken}" />
				<input type="hidden" name="file" value="{if $addon.directory}{$addon.directory}{else}{$addon.INSTALL.directory}{/if}" />
				<input type="submit" name="upgrade_module" class="fc_gradient_red" value="{translate('Execute upgrade.php manually')}" />
			</form>
{/if}

			<div class="clear_sp"></div>
		</div>




