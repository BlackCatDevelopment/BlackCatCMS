<div id="fc_content_header">
	<a class="fc_button_back ui-corner-right" target="_top" href="{$REDIRECT}" title="{translate('Next')}">{translate('Back')}</a>
	{translate('Success')}
</div>
<div class="fc_success_box">
	<p>{$MESSAGE}</p>
	<div class="fc_fallback">
		{if $REDIRECT_TIMER}
		<script type="text/javascript">
			setTimeout("top.location.href ='{$REDIRECT}'", {$REDIRECT_TIMER});
		</script>
		{/if}
	</div>
</div>
{if $REDIRECT}
<div class="fc_fallback fc_gradient4">
	<a target="_top" class="fc_button_back ui-corner-right" href="{$REDIRECT}">{translate('Back')}</a>
</div>
{/if}