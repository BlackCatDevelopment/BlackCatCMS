<div class="info">
{translate('Optional addons')}
</div>

{translate('The following optional addons were found in the <i>./optional</i> subfolder. Please check the ones you wish to install. (Default: all)')}<br /><br />

<ul>
    {foreach $zip_files file}
    <li> <input type="checkbox" checked="checked" id="installer_optional_addon" name="installer_optional_addon[]" value="{$file}" {if cat($config.default_wysiwyg,'.zip') == cat('opt_',$file) }disabled="disabled"{/if}/>
{if cat($config.default_wysiwyg,'.zip') == cat('opt_',$file) }<strong>{/if}
        {$file}
{if cat($config.default_wysiwyg,'.zip') == cat('opt_',$file) }</strong>{/if}
    </li>
    {/foreach}
</ul>
