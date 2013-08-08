<div id="fc_content_header">
	<a class="fc_button_back ui-corner-right" target="_top" href="{$LINK}" title="{translate('Next')}">{translate('Back')}</a>
	{translate('An error occured')}
</div>

<div id="fc_main_content" style="max-height: 462px;">
    <div class="fc_error_box warning ui-corner-all ui-shadow">
		{$MESSAGE}
    </div>
    {if $LINK}
	<div class="fc_fallback">
		<a target="_top" class="fc_button_back ui-corner-right" href="{$LINK}">{translate('Back')}</a>
	</div>
    {/if}
</div>

