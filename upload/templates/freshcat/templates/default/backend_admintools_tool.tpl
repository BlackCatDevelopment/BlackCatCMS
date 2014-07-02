<div id="fc_content_header">
	<a href="{$CAT_ADMIN_URL}/admintools/index.php" class="fc_button_back ui-corner-right">{translate('Back')}</a> <span class="fc_nostyle">{translate('Administration Tools')} =></span> {$TOOL_NAME}
    {if $ICON}<div style="float:right;padding-top:5px;"><img src="{$ICON}" alt="Icon" /></div>{/if}
</div>
<div id="fc_main_content">
	<div class="fc_tool">
		{$TOOL}
	</div>
</div>
