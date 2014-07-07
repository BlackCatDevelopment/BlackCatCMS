<div id="fc_content_header">
	<a class="fc_button_back ui-corner-right" target="_top" href="{$link}" title="{translate('Next')}">{translate('Back')}</a>
	{translate('An error occured')}
</div>

<div id="fc_main_content" style="max-height: 462px;">
    <div class="fc_error_box warning ui-corner-all ui-shadow">
        {translate('Ooops... A fatal error occured while processing your request!')}<br /><br />
        {translate($message)}<br /><br />
        {if $file}
        <span style="font-size: smaller;">{translate('Source')}: [ {$file} : {$line} : {$function} ]</span><br /><br />
        {/if}
    </div>
    {if $link}
	<div class="fc_fallback fc_gradient4">
		<a target="_top" class="fc_button_back ui-corner-right" href="{$link}">{translate('Back')}</a>
	</div>
    {/if}
</div>

