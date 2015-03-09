{include('backend_pages_header.tpl')}
<div id="fc_main_content">
	<div class="fc_modified_header">
		<div class="fc_current_page">
			{translate('Current page')}: <strong>{$PAGE_TITLE}</strong> (<strong>ID: <span id="fc_headers_pageid">{$PAGE_ID}</span></strong>)
		</div>
		<div class="fc_modified">
			{if $MODIFIED_WHEN}
			{translate('Last modification by')} {$MODIFIED_BY} ({$MODIFIED_BY_USERNAME}), {$MODIFIED_WHEN}
			{/if}
		</div>
		<div class="clear"></div>
	</div><br />

    <div class="fc_info" style="margin-left:15px;">
    {translate('You can manage Javascript- and CSS-Files resp. jQuery plugins to be loaded into the page header here.')}<br />
    {translate('Please note that there is a bunch of files that is loaded automatically, so there\'s no need to add them here.')}<br />
    {translate('These settings are page based, to manage global settings, goto Settings -> Header files.')}
    </div><br /><br />

{include('backend_manage_headerfiles.tpl')}

</div>