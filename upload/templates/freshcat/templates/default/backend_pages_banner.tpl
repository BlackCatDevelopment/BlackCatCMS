	<div class="fc_modified_header fc_gradient1 fc_border">
		<div class="fc_current_page">
			{translate('Current page')}: <strong>{$PAGE_TITLE}</strong> (<strong>ID: {$PAGE_ID}</strong>)
		</div>
		<div class="fc_modified">
			{if $MODIFIED_WHEN}
			{translate('Last modification by')} {$MODIFIED_BY} ({$MODIFIED_BY_USERNAME}), {$MODIFIED_WHEN}
			{/if}
		</div>
		<div class="clear"></div>
	</div>
