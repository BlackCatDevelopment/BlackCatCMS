<div id="fc_content_header">
	{translate('Admintools')}
</div>

<div id="fc_main_content">
	<ul id="fc_admintools">
		{foreach $tools as tool}
		<li class="fc_gradient1 fc_border">
			<a href="{$CAT_ADMIN_URL}/admintools/tool.php?tool={$tool.TOOL_DIR}" class="fc_admintool_link">
				{if $tool.ICON}<img src="{$tool.ICON}" alt="{$tool.TOOL_DIR}" />
				{else}<span class="fc_admintool_icon icon-wrench"></span>{/if}
				<span class="fc_admintool_title">{$tool.TOOL_NAME}</span>
			</a>
			<div class="fc_admintool_description">{$tool.TOOL_DESCRIPTION}</div>
		</li>
		{/foreach}
	</ul>
</div>
