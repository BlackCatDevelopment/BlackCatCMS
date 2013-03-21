<div id="bcversion">
    {if $error}<div class="widget_error">{$error}</div>{/if}
    {if $newer}<div class="widget_info">{translate('A newer version is available!')}</div>
    {else}<div class="widget_info">{translate("You're up-to-date!")}</div>
    {/if}
    <span style="display:inline-block;width:70%;">{translate('Local version')}:</span>{$CAT_VERSION}<br />
    <span style="display:inline-block;width:70%;">{translate('Remote version')}:</span>{$version}<br />
</div>