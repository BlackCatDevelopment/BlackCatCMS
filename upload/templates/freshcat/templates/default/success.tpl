<div id="fc_content_header">
	<a class="fc_button_back ui-corner-right" target="_top" href="{$REDIRECT}" title="{translate('Next')}">{translate('Back')}</a>
	{translate('Success')}
</div>
<div class="fc_success_box ui-corner-all ui-shadow" style="width:90%;">
    <span class="icon icon-info"></span>
    <span>{$MESSAGE}</span>
</div>
{if $REDIRECT}
<div class="fc_fallback fc_gradient4">
	<a target="_top" class="fc_button_back ui-corner-right" href="{$REDIRECT}">{translate('Back')}</a>
</div>
{/if}
{if $REDIRECT_TIMER}
<script type="text/javascript">
	setTimeout("top.location.href ='{$REDIRECT}'", {$REDIRECT_TIMER});
</script>
{/if}
