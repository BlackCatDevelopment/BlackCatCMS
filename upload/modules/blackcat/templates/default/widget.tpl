<div id="bcversion">
    {if $error || $version == 'unknown'}
    {if $error}<div class="widget_error">{$error}</div>{/if}
    <div class="widget_info widget_error" style="color:#f00;">{translate('Version check failed!')}</div>
    {else}
    {if ! $newer && $version !== 'unknown'}<div class="widget_info">{translate("You're up-to-date!")}</div>{/if}
    {if $newer}<div class="widget_info" style="color:#f00;">{translate('A newer version is available!')}</div>{/if}
    {/if}
    <span style="display:inline-block;width:70%;">{translate('Local version')}:</span>{$CAT_VERSION}<br />
    <span style="display:inline-block;width:70%;">{translate('Remote version')}:</span>{$version}<br />
    <span style="display:inline-block;width:70%;">{translate('Last checked')}:</span>{$last}<br /><br />
    <form method="get" action="{$uri}" style="float:right;">
      <input type="hidden" name="widget" value="blackcat" />
      <input type="submit" name="blackcat_refresh" value="{translate('Refresh now')}" />
    </form>

    <br clear="right" /><br />
    {if $missing_wysiwyg == 0} <div class="widget_info widget_error" style="color:#f00;">{translate('Warning: no WYSIWYG Editors installed!')}</div>{/if}
    {if $missing_mailer_libs == 0} <div class="widget_info widget_error" style="color:#f00;">{translate('Warning: no mailer libs installed!')}</div>{/if}

    [ <a href="{$CAT_ADMIN_URL}/admintools/tool.php?tool=blackcat">{translate('Edit connection settings')}</a>
    | <a href="http://blackcat-cms.org/page/download.php" target="_blank"{if $newer} style="color:#f00;font-weight:bold;"{/if}>{translate('Visit download page')}</a> ]
</div>