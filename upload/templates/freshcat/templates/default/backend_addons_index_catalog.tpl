<p class="fc_gradient1" style="text-align:right;border-bottom:1px solid #000;">
    {if $catalog_version}{translate('Catalog version')}: {$catalog_version}{/if}
    <button id="fc_addons_update_catalog">{translate('Update')}</button>
</p><div class="clear_sp"></div>

<table class="fc_table">
    <thead>
        <tr class="fc_gradient1">
            <th></th>
            <th>{translate('Addon')}</th>
            <th>{translate('Avail. since')}</th>
            <th>{translate('Current version')}</th>
            <th>{translate('Installed version')}</th>
            <th>{translate('Action')}</th>
        </tr>
    </thead>
    <tbody>
    {foreach $addons as addon}
        <tr>
            <td>
                {if $addon.installed_data.update === true}
                <span class="icon icon-alarm fc_addon_icon-alarm" title="{translate('Update available!')}"></span>
                {/if}
            </td>
            <td>
                <span class="fc_addon_name">{$addon.name}</span><br />
                {if $addon.description.en}{$addon.description.en.title}{/if}
            </td>
            <td>{if $addon.since}{$addon.since}{/if}</td>
            <td><span class="fc_addon_version{if $addon.installed_data.update == true} fc_addon_update{/if}">{$addon.version}</span></td>
            <td>
                {if $addon.is_installed}
                    <span class="fc_addon_version{if $addon.installed_data.update == true} fc_addon_old{/if}">{$addon.installed_data.version}</span><br />
                    <span class="fc_addon_installdate">{$addon.installed_data.install_date}</span>
                {/if}
            </td>
            <td class="fc_addon_buttons">
                {if ! $addon.is_installed && $permissions.MODULES_INSTALL == true}
                <button class="fc_catalog_install">{translate('Install')}</button>
                {/if}
                {if $addon.installed_data.update == true && $permissions.MODULES_INSTALL == true}
                <button>{translate('Update')}</button>
                {/if}
                {if $addon.is_installed && $addon.removable == 'Y' && $permissions.MODULES_UNINSTALL}
                <button>{translate('Uninstall')}</button>
                {/if}
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>

<script charset=iso-8859-1 type="text/javascript">
    jQuery(document).ready(function($) {
        $('#fc_addons_update_catalog').unbind('click').bind('click',function() {
            $.ajax({
    			type:		'GET',
    			url:		CAT_ADMIN_URL + '/addons/ajax_update_catalog.php',
    			dataType:	'json',
    			cache:		false,
                beforeSend:	function( data )
        		{
        			data.process	= set_activity();
        		},
    			success:	function( data, textStatus, jqXHR )
    			{
    				if ( data.success === true )
    				{
                        $('div#addons_main_content').html(data.content);
                        jqXHR.process.slideUp(1200, function(){ jqXHR.process.remove(); });
    				}
    				else {
    					return_error( jqXHR.process , data.message);
    				}
    			}
    		});
        });
        $('.fc_catalog_install').unbind('click').bind('click',function() {
            $.ajax({
    			type:		'GET',
    			url:		CAT_ADMIN_URL + '/addons/ajax_catalog_install.php',
    			dataType:	'json',
                data:       { name: $(this).parent().parent().find('.fc_addon_name').text() },
    			cache:		false,
                beforeSend:	function( data )
        		{
        			data.process	= set_activity();
        		},
    			success:	function( data, textStatus, jqXHR )
    			{
    				if ( data.success === true )
    				{
                        $('div#addons_main_content').html(data.content);
                        jqXHR.process.slideUp(1200, function(){ jqXHR.process.remove(); });
    				}
    				else {
    					return_error( jqXHR.process , data.message);
    				}
    			}
    		});
        });
    });
</script>