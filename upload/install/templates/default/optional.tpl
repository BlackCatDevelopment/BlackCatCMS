<div class="info">
{translate('Optional addons')}
{if $info}<br />{$info}<br />{/if}
</div>

{translate('The following optional addons were found in the <i>./optional</i> subfolder. Please check the ones you wish to install. (Default: all)')}<br /><br />

<ul>
    {foreach $zip_files file}
    <li>
        {if cat($config.default_wysiwyg,'.zip') == cat('opt_',$file) }
        <input type="checkbox" checked="checked" disabled="disabled" />
        <input type="hidden" id="installer_optional_addon" name="installer_optional_addon[]" value="{$file}" />
        {else}
        <input type="checkbox" checked="checked" id="installer_optional_addon" name="installer_optional_addon[]" value="{$file}" />
        {/if}
        {$file}
        {if cat($config.default_wysiwyg,'.zip') == cat('opt_',$file) }
        <span style="float:right;">{translate("This one can't be unchecked because you chose to use it!")}</span>
        {/if}
    </li>
    {/foreach}
</ul>
